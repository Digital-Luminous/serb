import { debounceEvent } from '../utils';

const elSelector = {
  searchInput: '.js-locations-search-input',
  locationSelect: '.js-location-select',
  locationsList: '.js-locations-list',
};

const element = {
  locationsList: document.querySelector(elSelector.locationsList),
  searchInput: document.querySelector(elSelector.searchInput),
  select: document.querySelector(elSelector.locationSelect),
};

const locationBoxDefaults = {
  continent: 'Continent',
  country: 'Country',
  bgColor: '#FCE0D3',
}

const Locations = {
  locationsByFilter: [],
  allLocations: [],
  locationsList: null,
  searchInput: null,
  select: null,
  searchTerm: '',
  filterBy: '',

  handleInputValue(e) {
    Locations.searchTerm = e.target.value;
    Locations.handleFilterLocations(Locations.getLocationsArr());
  },
  getLocationsArr() {
    return !!Locations.filterBy
      ? Locations.locationsByFilter
      : Locations.allLocations;
  },
  handleFilterLocations(locationsArr) {
    const locationsByFilter = locationsArr.filter((location) => {
      const searchedTerm = Locations.searchTerm.toLowerCase();

      return (
        location.continent?.toLowerCase().includes(searchedTerm) ||
        location.country?.toLowerCase().includes(searchedTerm)
      );
    });

    if (locationsByFilter.length) {
      Locations.locationsList.innerHTML =
        Locations.renderLocationsList(locationsByFilter);
    } else {
      Locations.locationsList.innerHTML = Locations.renderNotFoundInfo();
    }
  },
  handleSelectChange(e) {
    Locations.filterBy = e.target.value;
    Locations.locationsByFilter = Locations.allLocations.filter(
      (location) =>
        location.continent?.toLowerCase() === Locations.filterBy.toLowerCase()
    );
    Locations.locationsList.innerHTML = Locations.renderLocationsList(
      Locations.getLocationsArr()
    );
    Locations.searchInput.value = '';
  },
  addEventListeners() {
    Locations.searchInput.addEventListener(
      'keyup',
      debounceEvent((e) => Locations.handleInputValue(e))
    );
    Locations.select.addEventListener('change', Locations.handleSelectChange);
  },
  async fetchLocations() {
    try {
      const apiURL = Locations.locationsList.dataset.locationsApi;
      const response = await fetch(apiURL);
      Locations.allLocations = await response.json();

      Locations.locationsList.innerHTML = Locations.renderLocationsList(
        Locations.allLocations
      );
    } catch (error) {
      console.error(error);
    }
  },
  renderNotFoundInfo() {
    return `<span>No results</span>`;
  },
  renderLocationsList(locationsList) {
    return locationsList
      .map((location) => {
        const {
          continent,
          country,
          addressDetails,
          contactDetails,
          contactUrl,
          contactLabel,
          bgColor,
          hideButton
        } = location;

        return `
        <li class="c-locations-list__item" style="background-color: ${
          bgColor || locationBoxDefaults.bgColor
        }">
        <div class="c-location-box u-full-width js-location" data-continent="${
          continent || locationBoxDefaults.continent
        }">
          <h6 class="c-location-box__continent t-size-24 ui-color--purple-1 ui-font-weight--semibold">${
            continent || locationBoxDefaults.continent
          }</h6>
          <h4 class="c-locatio-box__country t-size-24 ui-color--black-1 ui-font-weight--semibold">${
            country || locationBoxDefaults.country
          }</h4>
          ${
            addressDetails.length || contactDetails.length ? (
             `<div class="c-location-box__content t-size-20  ui-color--dark-grey-2">
                <ul class="c-location-box__info-list c-location-box__info-list--address">
                  ${addressDetails &&
                    addressDetails
                      .map(
                        (address) =>
                          `<li class="c-location-box__info">${address}</li>`
                      )
                      .join('')}
                </ul>
                <ul class="c-location-box__info-list c-location-box__info-list--contact">
                  ${contactDetails &&
                    contactDetails
                      .map(
                        (contact) =>
                          `<li class="c-location-box__info"><div class="c-cms-content">${contact}</div></li>`
                      )
                      .join('')}
                </ul>
              </div>`
            ) : (
              ''
            )
          }
          ${ !hideButton ? `<a href="${contactUrl}" target="_blank" class="c-location-box__action c-btn c-btn--arrowed c-btn--secondary">
              ${contactLabel}</a>` : ''
            }

        </div>
      </li>`;
      })
      .join('');
  },
  init() {
    Locations.locationsList = element.locationsList;
    Locations.searchInput = element.searchInput;
    Locations.select = element.select;

    const shouldContinue = !!Locations.searchInput && !!Locations.locationsList;

    if (!shouldContinue) return;

    Locations.fetchLocations();
    Locations.addEventListeners();
  },
};

Locations.init();
