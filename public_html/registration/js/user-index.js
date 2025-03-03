/**
 * Scripts for the user index page.
 */
const app = (function() {
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

  // region variables

  /**
   * @type {HTMLButtonElement}
   */
  const m_previousButton = document.getElementById('workshop-cards-previous-button');

  /**
   * @type {HTMLButtonElement}
   */
  const m_nextButton = document.getElementById('workshop-cards-next-button');

  /**
   * @type {HTMLButtonElement}
   */
  const m_submitButton = document.getElementById('workshop-card-submit-button');

  /**
   * @type {HTMLDivElement}
   */
  const m_container = document.getElementById('workshop-cards-container');

  /**
   * @type {HTMLTemplateElement}
   */
  const m_template = document.getElementById('workshop-card-template');

  /**
   * @type {HTMLInputElement}
   */
  const m_workshopIdInput = document.getElementById('workshop-card-workshop-id');

  /**
   * @type {HTMLInputElement}
   */
  const m_participantIdInput = document.getElementById('workshop-card-participant-id');

  /**
   * @type {HTMLDivElement}
   */
  const m_loading = document.getElementById('workshop-card-loading');

  /**
   * @type {HTMLDivElement}
   */
  const m_none = document.getElementById('workshop-card-none');

  /**
   * @type {HTMLDialogElement}
   */
  let m_dialog;

  /**
   * @type {boolean}
   */
  let m_busy = true;

  /**
   * @type {boolean}
   */
  let m_dragging = false;

  /**
   * @type {number}
   */
  let m_dragStartX = 0;

  /**
   * @type {number}
   */
  let m_dragStartScrollLeft = 0;

  /**
   * @type string
   */
  let m_workshopUrl;

  // endregion

  // region initialization

  m_previousButton.addEventListener('click', handlePreviousClick);
  m_nextButton.addEventListener('click', handleNextClick);
  m_container.addEventListener('mousedown', handleMouseDown);
  document.addEventListener('mouseup', handleMouseUp, true);
  document.addEventListener('mousemove', handleMouseMove, true);
  m_container.addEventListener('scroll', handleScroll);

  // endregion

  // region functions

  /**
   * Updates the workshop id input field based on the current scroll position.
   */
  function updateWorkshopId() {
    const index = Math.floor(m_container.scrollLeft / m_container.clientWidth) + 2;
    const card = m_container.children[index];
    if (card) {
      m_workshopIdInput.value = card.getAttribute(DATA_WORKSHOP_ID);
    }
  }

  /**
   * Starts loading the workshops for the current participant id.
   *
   * @returns {Promise<void>}
   */
  async function startLoading() {
    const participantId = m_participantIdInput.value;
    const result = await fetch(`${m_workshopUrl}/${participantId}`)
    const json = await result.json();
    m_loading.classList.add(CSS_CENTERED_TEXT_HIDDEN);
    processWorkshopsResponse(json);
    m_busy = false;
    updateWorkshopId();
    updateButtons();
  }

  /**
   * Processes the workshops response. Either show none message or create html cards.
   *
   * @param {WorkshopResponse} workshopResponse
   */
  function processWorkshopsResponse(workshopResponse) {
    if (workshopResponse.workshops.length === 0) {
      m_none.classList.remove(CSS_CENTERED_TEXT_HIDDEN);
      return;
    }
    for(const workshop of workshopResponse.workshops) {
      const card = createWorkshopCard(workshop);
      m_container.appendChild(card);
    }
  }

  /**
   * Creates card for a workshop
   *
   * @param {Workshop} workshop
   */
  function createWorkshopCard(workshop) {
    const card = m_template.content.cloneNode(true);
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
    }
    else if (workshop.waiting === 0) {
      available.remove();
      waiting.remove();
    }
    else {
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
  function updateButtons() {
    m_previousButton.disabled =
      (m_container.scrollLeft < 50) ||
      m_busy ||
      (m_container.childElementCount < 3);
    m_nextButton.disabled =
      (m_container.scrollLeft + m_container.clientWidth >= m_container.scrollWidth - 50) ||
      m_busy ||
      (m_container.childElementCount < 3);
    m_submitButton.disabled = m_busy || (m_container.childElementCount < 3);
  }

  // endregion

  // region event handlers

  function handlePreviousClick() {
    m_container.scrollBy({
      left: -m_container.clientWidth,
      behavior: 'smooth'
    });
  }

  function handleNextClick() {
    m_container.scrollBy({
      left: m_container.clientWidth,
      behavior: 'smooth'
    });
  }

  function handleMouseDown(event) {
    if (m_dragging || m_busy) {
      return;
    }
    event.preventDefault();
    m_dragging = true;
    m_container.classList.add('cd-workshop-card__container--is-dragging');
    m_dragStartX = event.pageX;
    m_dragStartScrollLeft = m_container.scrollLeft;
  }

  function handleMouseUp() {
    if (!m_dragging) {
      return;
    }
    m_dragging = false;
    m_container.classList.remove('cd-workshop-card__container--is-dragging');
  }

  function handleMouseMove(event) {
    if (!m_dragging) {
      return;
    }
    event.preventDefault();
    const delta = event.pageX - m_dragStartX;
    m_container.scrollLeft = m_dragStartScrollLeft - delta;
  }

  function handleScroll() {
    updateWorkshopId();
    updateButtons();
  }

  function handleToggleDialog() {
    if (!m_dialog.open) {
      return;
    }
    m_busy = true;
    m_dragging = false;
    m_previousButton.disabled = true;
    m_nextButton.disabled = true;
    Array.from(m_container.children).forEach(child => {
      if ((child.id !== m_loading.id) && (child.id !== m_none.id)) {
        m_container.removeChild(child);
      }
    });
    m_loading.classList.remove(CSS_CENTERED_TEXT_HIDDEN);
    m_none.classList.add(CSS_CENTERED_TEXT_HIDDEN);
    m_container.scrollLeft = 0;
    updateButtons();
    // load with next update, to be sure participant id has been set by other scripts
    setTimeout(startLoading, 0);
  }

  // endregion

  return {
    /**
     * Initializes the app.
     *
     * @param dialogId
     *   Id of dialog
     * @param workshopUrl
     *   Url to get workshops from (the code will add / + participant id).
     */
    init(dialogId, workshopUrl) {
      m_dialog = document.getElementById(dialogId);
      m_dialog.addEventListener('toggle', handleToggleDialog);
      m_workshopUrl = workshopUrl;
    }
  };
})();