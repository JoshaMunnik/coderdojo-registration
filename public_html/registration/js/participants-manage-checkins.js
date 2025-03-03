/**
 * Scripts for the user index page.
 */
const app = (function () {
  // region types

  /**
   * @typedef {Object} CheckinResponse
   * @property {boolean} success
   * @property {boolean} checked_in
   */

  // endregion

  // region variables

  /**
   * Checkin 'buttons'
   *
   * @type {NodeListOf<HTMLInputElement>}
   */
  const m_checkinInputs = document.querySelectorAll('[data-checkin-button]');

  /**
   * CSRF token for form submissions.
   *
   * @type {string}
   */
  let m_csrfToken;

  /**
   * @type {string}
   */
  let m_checkinUrl;

  // endregion

  // region initialization

  for (const checkinInput of m_checkinInputs) {
    checkinInput.addEventListener('change', () => handleCheckinInputChange(checkinInput));
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
  async function processCheckin(checkin, participantId) {
    const formData = new FormData;
    formData.append('checked_in', checkin ? '1' : '0');
    formData.append('participant_id', participantId);
    // Send the form data using fetch
    const response = await fetch(m_checkinUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-Token': m_csrfToken
      },
      body: formData
    });
    if (response.ok) {
      /** @type {CheckinResponse} */
      const result = await response.json();
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
  async function handleCheckinInputChange(checkinInput) {
    checkinInput.disabled = true;
    checkinInput.checked = await processCheckin(
      checkinInput.checked,
      checkinInput.getAttribute('data-participant-id')
    );
    const td = checkinInput.closest('td');
    td.setAttribute('data-uf-sort-value', checkinInput.checked ? '1' : '0');
    checkinInput.disabled = false;
  }

  // endregion

  return {
    /**
     * Initializes the app.
     *
     * @param checkinUrl
     *   Url to send checkin request to.
     * @param csrfToken
     *   Csrf token for form submissions.
     */
    init(checkinUrl, csrfToken) {
      m_checkinUrl = checkinUrl;
      m_csrfToken = csrfToken;
    }
  };
})();