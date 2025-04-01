/**
 * Module to handle the participants manage checkin page.
 */

// region types

/**
 * @typedef {Object} CheckinResponse
 * @property {boolean} success
 * @property {boolean} checked_in
 */

// endregion

// region class

/**
 * Scripts for the user index page.
 */
class ParticipantsManageCheckins {
  // region variables

  /**
   * Checkin 'buttons'
   *
   * @type {NodeListOf<HTMLInputElement>}
   */
  #m_checkinInputs = document.querySelectorAll('[data-checkin-button]');

  /**
   * CSRF token for form submissions.
   *
   * @type {string}
   */
  #m_csrfToken;

  /**
   * @type {string}
   */
  #m_checkinUrl;

  // endregion

  // region public methods

  /**
   * Initializes the app.
   *
   * @param checkinUrl
   *   Url to send checkin request to.
   * @param csrfToken
   *   Csrf token for form submissions.
   */
  init(checkinUrl, csrfToken) {
    this.#m_checkinUrl = checkinUrl;
    this.#m_csrfToken = csrfToken;
    for (let checkinInput of this.#m_checkinInputs) {
      checkinInput.addEventListener(
        'change', () => this.#handleCheckinInputChange(checkinInput)
      );
    }
  }

  // endregion

  // region functions

  /**
   * Processes a checkin.
   *
   * @param {boolean} checkin
   * @param {string} participantId
   *
   * @returns {Promise<boolean>}
   */
  async #processCheckin(checkin, participantId) {
    let formData = new FormData;
    formData.append('checked_in', checkin ? '1' : '0');
    formData.append('participant_id', participantId);
    // Send the form data using fetch
    let response = await fetch(this.#m_checkinUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-Token': this.#m_csrfToken
      },
      body: formData
    });
    if (response.ok) {
      /** @type {CheckinResponse} */
      let result = await response.json();
      return result.checked_in;
    }
    console.error('Checkin submission failed:', response.statusText);
    return checkin;
  }

  // endregion

  // region event handlers

  /**
   * Handles changes to checkin inputs.
   *
   * @param {HTMLInputElement} checkinInput
   */
  async #handleCheckinInputChange(checkinInput) {
    checkinInput.disabled = true;
    checkinInput.checked = await this.#processCheckin(
      checkinInput.checked,
      checkinInput.getAttribute('data-participant-id')
    );
    const td = checkinInput.closest('td');
    if (td) {
      td.setAttribute('data-uf-sort-value', checkinInput.checked ? '1' : '0');
    }
    checkinInput.disabled = false;
  }
}

// endregion

// region exports

export const participantsManageCheckins = new ParticipantsManageCheckins();

// endregion