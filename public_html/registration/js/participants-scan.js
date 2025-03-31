/**
 * Module to handle the scanning of user qr codes page.
 */

// region imports

import QrScanner from './qr-scanner.min.js';

// endregion

// region constants

const MESSAGE_HIDE_CSS_CLASS = 'cd-scan__message--is-hidden';

const USER_HIDE_CSS_CLASS = 'cd-scan__user--is-hidden';

const SECTION_HIDE_CSS_CLASS = 'cd-scan__section--is-hidden';

// endregion

// region types

/**
 * @typedef {Object} CheckinResponse
 * @property {boolean} success
 * @property {boolean} checked_in
 */

/**
 * @typedef {Object} CameraEntry
 * @property {string} id
 * @property {string} label
 */

// endregion

// region class

/**
 * Scripts for the user index page.
 */
class ParticipantsScan {
  // region variables

  // region private variables

  /**
   * @type {QrScanner}
   */
  #m_qrScanner;

  /**
   * @type {HTMLElement}
   */
  #m_webcamSection = document.getElementById('webcam-section');

  /**
   * @type {HTMLElement}
   */
  #m_userSection = document.getElementById('user-section');

  /**
   * @type {HTMLElement}
   */
  #m_webcamView = document.getElementById('webcam-view');

  /**
   * @type {HTMLElement}
   */
  #m_waitingMessage = document.getElementById('waiting-message');

  /**
   * @type {HTMLElement}
   */
  #m_unknownMessage = document.getElementById('unknown-message');

  /**
   * @type {HTMLButtonElement}
   */
  #m_nextButton = document.getElementById('next-button');

  /**
   * @type {HTMLSelectElement}
   */
  #m_webcamSelect = document.getElementById('webcam-select');

  /**
   * @type {HTMLElement|null}
   */
  #m_selectedUser= null;

  /**
   * @type {string}
   */
  #m_lastScannedCode = '';

  /**
   * @type {boolean}
   */
  #m_scanning = false;

  /**
   * @type {null}
   */
  #m_unknownTimer = null;

  // endregion

  // region public methods

  /**
   * Initializes the scanner.
   */
  async init() {
    await this.#initializeScanner();
    await this.#setCameras();
    this.#m_nextButton.addEventListener('click', () => this.#handleNextClick());
    this.#m_webcamSelect.addEventListener('change', () => this.#handleWebcamChange());
  }

  // endregion

  // region private methods

  /**
   * Initializes and starts the qr scanner.
   *
   * @returns {Promise<void>}
   */
  async #initializeScanner() {
    this.#m_qrScanner = new QrScanner(
      this.#m_webcamView,
      result => this.#handleScannerCode(result),
      {
        /* your options or returnDetailedScanResult: true if you're not specifying any other options */
        returnDetailedScanResult: true
      },
    );
    await this.#m_qrScanner.start();
  }

  /**
   * Tries to find a user with the given code. Hide the scanner if a user is found.
   *
   * @param code
   */
  #processCode(code) {
    this.#m_selectedUser = document.querySelector(`[data-user-id="${code}"]`);
    if (this.#m_selectedUser) {
      this.#stopScanning();
      this.#showSelectedUser();
    }
    else {
      this.#showUnknownCode();
    }
  }

  /**
   * Shows the webcam and the waiting message.
   */
  #startScanning() {
    this.#m_scanning = true;
    this.#m_webcamSection.classList.remove(SECTION_HIDE_CSS_CLASS);
    this.#m_waitingMessage.classList.remove(MESSAGE_HIDE_CSS_CLASS);
    this.#m_unknownMessage.classList.add(MESSAGE_HIDE_CSS_CLASS);
  }

  /**
   * Shows the unknown message for 3 seconds.
   */
  #showUnknownCode() {
    this.#stopUnknownTimer();
    this.#m_waitingMessage.classList.add(MESSAGE_HIDE_CSS_CLASS);
    this.#m_unknownMessage.classList.remove(MESSAGE_HIDE_CSS_CLASS);
    this.#m_unknownTimer = setTimeout(() => {
      this.#hideUnknownCode();
    }, 3000);

  }

  /**
   * Hides the unknown message.
   */
  #hideUnknownCode() {
    this.#stopUnknownTimer();
    this.#m_waitingMessage.classList.remove(MESSAGE_HIDE_CSS_CLASS);
    this.#m_unknownMessage.classList.add(MESSAGE_HIDE_CSS_CLASS);
  }

  /**
   * Stops the unknown timer if it is running.
   */
  #stopUnknownTimer() {
    if (this.#m_unknownTimer) {
      clearTimeout(this.#m_unknownTimer);
      this.#m_unknownTimer = null;
    }
  }

  /**
   * Stops the scanning process and hides the webcam and messages.
   */
  #stopScanning() {
    this.#m_scanning = false;
    this.#m_webcamSection.classList.add(SECTION_HIDE_CSS_CLASS);
    this.#m_waitingMessage.classList.add(MESSAGE_HIDE_CSS_CLASS);
    this.#m_unknownMessage.classList.add(MESSAGE_HIDE_CSS_CLASS);
    this.#stopUnknownTimer();
  }

  /**
   * Shows the selected user and the next button.
   */
  #showSelectedUser() {
    this.#m_userSection.classList.remove(SECTION_HIDE_CSS_CLASS);
    this.#m_selectedUser.classList.remove(USER_HIDE_CSS_CLASS);
  }

  /**
   * Clears the selected user and hides the next button.
   */
  #clearSelectedUser() {
    if (this.#m_selectedUser) {
      this.#m_selectedUser.classList.add(USER_HIDE_CSS_CLASS);
      this.#m_selectedUser = null;
    }
    this.#m_userSection.classList.add(SECTION_HIDE_CSS_CLASS);
  }

  /**
   * Initializes the camera dropdown list.
   *
   * @returns {Promise<void>}
   */
  async #setCameras() {
    /** @type {CameraEntry[]} */
    const entries = await QrScanner.listCameras(true);
    if (entries.length === 0) {
      this.#m_webcamSelect.innerHTML = '<option>(no cameras found)</option>';
      return;
    }
    let html = '';
    for(const entry of entries) {
      html += `<option value="${entry.id}">${entry.label}</option>`;
    }
    this.#m_webcamSelect.innerHTML = html;
    this.#m_webcamSelect.value = entries[0].id;
  }

  // endregion

  // region event handlers

  /**
   * Handles a new scanner code.
   *
   * @param result
   */
  #handleScannerCode(result) {
    if (!this.#m_scanning) {
      return;
    }
    if (result.hasOwnProperty('data')) {
      const newScannedCode = result.data;
      if (newScannedCode !== this.#m_lastScannedCode) {
        this.#m_lastScannedCode = newScannedCode;
        this.#processCode(newScannedCode);
      }
    }
  }

  /**
   * Handles the click on the next button.
   */
  #handleNextClick() {
    this.#clearSelectedUser();
    this.#startScanning();
  }

  /**
   * Handles the change of the webcam select element.
   */
  #handleWebcamChange() {
    const selectedCameraId = this.#m_webcamSelect.value;
    if (this.#m_qrScanner) {
      this.#m_qrScanner.setCamera(selectedCameraId);
    }
  }

  // endregion
}

// endregion

// region exports

export const participantsScan = new ParticipantsScan();

// endregion