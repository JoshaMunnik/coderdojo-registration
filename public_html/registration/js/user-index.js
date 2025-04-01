/**
 * Module to handle the user index page.
 */

// region types

/**
 * @typedef {Object} Workshop
 * @property {string} id
 * @property {string} name
 * @property {string} description
 * @property {number} available
 * @property {number} waiting
 */

/**
 * @typedef {Object} WorkshopResponse
 * @property {Workshop[]} workshops
 */

// endregion

// region constants

const DATA_WORKSHOP_ID = 'data-workshop-id';
const DATA_WORKSHOP_NAME = 'data-workshop-name';
const DATA_WORKSHOP_DESCRIPTION = 'data-workshop-description';
const DATA_WORKSHOP_AVAILABLE = 'data-workshop-available';
const DATA_WORKSHOP_FULL = 'data-workshop-full';
const DATA_WORKSHOP_WAITING = 'data-workshop-waiting';
const DATA_WORKSHOP_WAITING_VALUE = 'data-workshop-waiting-value';

const CSS_CENTERED_TEXT_HIDDEN = 'cd-workshop-card__centered-text--is-hidden';

// endregion

// region class

class UserIndex {
  // region variables

  /**
   * @type {HTMLButtonElement}
   */
  #m_previousButton = document.getElementById('workshop-cards-previous-button');

  /**
   * @type {HTMLButtonElement}
   */
  #m_nextButton = document.getElementById('workshop-cards-next-button');

  /**
   * @type {HTMLButtonElement}
   */
  #m_submitButton = document.getElementById('workshop-card-submit-button');

  /**
   * @type {HTMLDivElement}
   */
  #m_container = document.getElementById('workshop-cards-container');

  /**
   * @type {HTMLTemplateElement}
   */
  #m_template = document.getElementById('workshop-card-template');

  /**
   * @type {HTMLInputElement}
   */
  #m_workshopIdInput = document.getElementById('workshop-card-workshop-id');

  /**
   * @type {HTMLInputElement}
   */
  #m_participantIdInput = document.getElementById('workshop-card-participant-id');

  /**
   * @type {HTMLDivElement}
   */
  #m_loading = document.getElementById('workshop-card-loading');

  /**
   * @type {HTMLDivElement}
   */
  #m_none = document.getElementById('workshop-card-none');

  /**
   * @type {HTMLDialogElement}
   */
  #m_dialog;

  /**
   * @type {boolean}
   */
  #m_busy = true;

  /**
   * @type {boolean}
   */
  #m_dragging = false;

  /**
   * @type {number}
   */
  #m_dragStartX = 0;

  /**
   * @type {number}
   */
  #m_dragStartScrollLeft = 0;

  /**
   * @type string
   */
  #m_workshopUrl;

  #m_dialogObserver;

  // endregion

  // region public methods

  /**
   * Initializes the app.
   *
   * @param dialogId
   *   Id of dialog
   * @param workshopUrl
   *   Url to get workshops from (the code will add / + participant id).
   */
  init(dialogId, workshopUrl) {
    this.#m_dialog = document.getElementById(dialogId);
    //this.#m_dialog.addEventListener('toggle', () => this.#handleToggleDialog());
    // use observer instead of toggle event, because it is not supported in all browsers
    this.#m_dialogObserver = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'open') {
          this.#handleToggleDialog();
        }
      });
    });
    this.#m_dialogObserver.observe(this.#m_dialog, { attributes: true });
    this.#m_workshopUrl = workshopUrl;
    this.#m_previousButton.addEventListener('click', () => this.#handlePreviousClick);
    this.#m_nextButton.addEventListener('click', () => this.#handleNextClick());
    this.#m_container.addEventListener('mousedown', () => this.#handleMouseDown());
    document.addEventListener('mouseup', () => this.#handleMouseUp(), true);
    document.addEventListener('mousemove', () => this.#handleMouseMove(), true);
    this.#m_container.addEventListener('scroll', () => this.#handleScroll());
  }

  // endregion

  // region functions

  /**
   * Updates the workshop id input field based on the current scroll position.
   */
  #updateWorkshopId() {
    const index = Math.floor(this.#m_container.scrollLeft / this.#m_container.clientWidth)
      + 2;
    const card = this.#m_container.children[index];
    if (card) {
      this.#m_workshopIdInput.value = card.getAttribute(DATA_WORKSHOP_ID);
    }
  }

  /**
   * Starts loading the workshops for the current participant id.
   *
   * @returns {Promise<void>}
   */
  async #startLoading() {
    const participantId = this.#m_participantIdInput.value;
    const result = await fetch(`${this.#m_workshopUrl}/${participantId}`)
    const json = await result.json();
    this.#m_loading.classList.add(CSS_CENTERED_TEXT_HIDDEN);
    this.#processWorkshopsResponse(json);
    this.#m_busy = false;
    this.#updateWorkshopId();
    this.#updateButtons();
  }

  /**
   * Processes the workshops response. Either show none message or create html cards.
   *
   * @param {WorkshopResponse} workshopResponse
   */
  #processWorkshopsResponse(workshopResponse) {
    if (workshopResponse.workshops.length === 0) {
      this.#m_none.classList.remove(CSS_CENTERED_TEXT_HIDDEN);
      return;
    }
    for (const workshop of workshopResponse.workshops) {
      const card = this.#createWorkshopCard(workshop);
      this.#m_container.appendChild(card);
    }
  }

  /**
   * Creates card for a workshop
   *
   * @param {Workshop} workshop
   */
  #createWorkshopCard(workshop) {
    const card = this.#m_template.content.cloneNode(true);
    card.firstElementChild.setAttribute(DATA_WORKSHOP_ID, workshop.id);
    const name = card.querySelector(`[${DATA_WORKSHOP_NAME}]`);
    name.innerText = workshop.name;
    const description = card.querySelector(`[${DATA_WORKSHOP_DESCRIPTION}]`);
    description.innerHTML = workshop.description;
    const available = card.querySelector(`[${DATA_WORKSHOP_AVAILABLE}]`);
    const waiting = card.querySelector(`[${DATA_WORKSHOP_WAITING}]`);
    const full = card.querySelector(`[${DATA_WORKSHOP_FULL}]`);
    if (workshop.available !== 0) {
      waiting.remove();
      full.remove();
    } else if (workshop.waiting === 0) {
      available.remove();
      waiting.remove();
    } else {
      available.remove();
      full.remove();
      const value = waiting.querySelector(`[${DATA_WORKSHOP_WAITING_VALUE}]`);
      value.innerText = workshop.waiting;
    }
    return card;
  }

  /**
   * Updates the scroll buttons.
   */
  #updateButtons() {
    this.#m_previousButton.disabled =
      (this.#m_container.scrollLeft < 50) ||
      this.#m_busy ||
      (this.#m_container.childElementCount < 3);
    this.#m_nextButton.disabled =
      (
        this.#m_container.scrollLeft + this.#m_container.clientWidth >=
        this.#m_container.scrollWidth - 50
      ) ||
      this.#m_busy ||
      (this.#m_container.childElementCount < 3);
    this.#m_submitButton.disabled = this.#m_busy || (this.#m_container.childElementCount < 3);
  }

  // endregion

  // region event handlers

  #handlePreviousClick() {
    this.#m_container.scrollBy({
      left: -this.#m_container.clientWidth,
      behavior: 'smooth'
    });
  }

  #handleNextClick() {
    this.#m_container.scrollBy({
      left: this.#m_container.clientWidth,
      behavior: 'smooth'
    });
  }

  #handleMouseDown(event) {
    if (this.#m_dragging || this.#m_busy) {
      return;
    }
    event.preventDefault();
    this.#m_dragging = true;
    this.#m_container.classList.add('cd-workshop-card__container--is-dragging');
    this.#m_dragStartX = event.pageX;
    this.#m_dragStartScrollLeft = this.#m_container.scrollLeft;
  }

  #handleMouseUp() {
    if (!this.#m_dragging) {
      return;
    }
    this.#m_dragging = false;
    this.#m_container.classList.remove('cd-workshop-card__container--is-dragging');
  }

  #handleMouseMove(event) {
    if (!this.#m_dragging) {
      return;
    }
    event.preventDefault();
    const delta = event.pageX - this.#m_dragStartX;
    this.#m_container.scrollLeft = this.#m_dragStartScrollLeft - delta;
  }

  #handleScroll() {
    this.#updateWorkshopId();
    this.#updateButtons();
  }

  #handleToggleDialog() {
    if (!this.#m_dialog.open) {
      return;
    }
    this.#m_busy = true;
    this.#m_dragging = false;
    this.#m_previousButton.disabled = true;
    this.#m_nextButton.disabled = true;
    Array.from(this.#m_container.children).forEach(child => {
      if ((child.id !== this.#m_loading.id) && (child.id !== this.#m_none.id)) {
        this.#m_container.removeChild(child);
      }
    });
    this.#m_loading.classList.remove(CSS_CENTERED_TEXT_HIDDEN);
    this.#m_none.classList.add(CSS_CENTERED_TEXT_HIDDEN);
    this.#m_container.scrollLeft = 0;
    this.#updateButtons();
    // load with next update, to be sure participant id has been set by other scripts
    setTimeout(() => this.#startLoading(), 0);
  }
  // endregion
}

// endregion

// region exports

export const userIndex = new UserIndex();

// endregion
