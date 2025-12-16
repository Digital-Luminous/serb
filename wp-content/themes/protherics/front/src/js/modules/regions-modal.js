import Cookies from 'js-cookie';

const selector = {
  modal: '.js-regions-modal',
  closeModalBtn: '.js-regions-modal-close',
  acceptModalBtn: '.js-regions-modal-accept',
  openModalBtn: '.js-open-regions-modal',
  regionSelect: '.js-regions-select',
  regionName: '.js-region-name',
};

const state = {
  isOpen: 'is-open',
};

let params = new URLSearchParams(document.location.search);
let name = params.get("r");

let allRegionsList = prothericsObj.region_list;

const cookieConfiguration = {
    prothericsRegion: {
      name: 'protherics_region',
      expires: 0,
      defaultValue: prothericsObj.defaultRegion ? prothericsObj.defaultRegion : 'all',
    },
    prothericsRegionName: {
      name: 'protherics_region_name',
      expires: 0,
      defaultValue: name ? name : prothericsObj.defaultRegion,
    },
};

const RegionsModal = {
  modal: null,
  closeModalBtn: null,
  acceptModalBtn: null,
  openModalBtns: [],
  regionName: null,
  init: () => {
    RegionsModal.modal = document.querySelector(selector.modal);
    RegionsModal.closeModalBtn = document.querySelector(selector.closeModalBtn);
    RegionsModal.acceptModalBtn = document.querySelector(selector.acceptModalBtn);
    RegionsModal.openModalBtns = document.querySelectorAll(selector.openModalBtn);
    RegionsModal.regionName = document.querySelector(selector.regionName);

    if(name) {
      const [currentRegion] = allRegionsList.filter(region => region.region.toLowerCase() === name.toLowerCase());
      const currentRegionId = currentRegion.regionId;

      RegionsModal.regionName.textContent = name;
      RegionsModal.saveCookie(name, 'prothericsRegionName');
      RegionsModal.saveCookie(currentRegionId, 'prothericsRegion');
    }


    const shouldContinue = !!RegionsModal.modal && !!RegionsModal.closeModalBtn && !!RegionsModal.acceptModalBtn;

    if (!shouldContinue) return;

    if (!RegionsModal.getCookie('prothericsRegion')) {
      RegionsModal.showModal();
    }

    RegionsModal.setRegionName();
    RegionsModal.addEventListeners();
  },
  addEventListeners: () => {
    RegionsModal.closeModalBtn.addEventListener('click', RegionsModal.handleCloseModal);
    RegionsModal.acceptModalBtn.addEventListener('click', RegionsModal.handleAcceptModal);

    if (RegionsModal.openModalBtns.length) {
      RegionsModal.openModalBtns.forEach(btn => btn.addEventListener('click', RegionsModal.showModal));
    }
  },
  handleCloseModal: () => {
    const cookieValue = cookieConfiguration['prothericsRegion'].defaultValue;
    const regionNameCookie = cookieConfiguration['prothericsRegionName'].defaultValue;

    RegionsModal.saveCookie(cookieValue, 'prothericsRegion');
    RegionsModal.saveCookie(regionNameCookie, 'prothericsRegionName');
    RegionsModal.setRegionAttribute();
    RegionsModal.setRegionName();
    RegionsModal.hideModal();
    RegionsModal.refreshPage();
  },
  handleAcceptModal: async () => {
    console.log('=== handleAcceptModal called ===');
    
    let cookieValue = cookieConfiguration['prothericsRegion'].defaultValue;
    let regionNameCookie = cookieConfiguration['prothericsRegionName'].defaultValue;

    const regionSelect = document.querySelector(selector.regionSelect);
    const value = regionSelect ? regionSelect.value : null;
    
    console.log('Region select value:', value);
    console.log('Region select element:', regionSelect);
    
    if (value) {
      if (isNaN(value)) {
        // is url - redirect
        console.log('Handling URL redirect:', value);
        
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];
        regionNameCookie = selectedOption ? selectedOption.text : regionNameCookie;
        
        // Determine region ID from URL AND selected option
        const urlDomain = new URL(value).hostname;
        let redirectRegionId = cookieValue;
        
        // Map domains to region IDs
        if (urlDomain.includes('serb.fr')) {
          redirectRegionId = 2000; // France
        } else if (urlDomain.includes('serb.be')) {
          redirectRegionId = 1999; // Belgium  
        } else if (urlDomain.includes('serb.com') || urlDomain.includes('serb.local')) {
          // For main domain, determine region from the selected option text
          const optionText = regionNameCookie.toLowerCase();
          if (optionText.includes('us')) {
            redirectRegionId = 2001; // US
          } else if (optionText.includes('global')) {
            redirectRegionId = 1998; // Global
          } else {
            redirectRegionId = 2001; // Default to US
          }
        }
        
        console.log('Setting cookies before redirect:', { 
          redirectRegionId, 
          regionNameCookie, 
          selectedOptionText: selectedOption ? selectedOption.text : 'none'
        });
        
        // Add URL parameters to pass the region info
        const redirectUrl = new URL(value);
        redirectUrl.searchParams.set('region_id', redirectRegionId);
        redirectUrl.searchParams.set('region_name', regionNameCookie);
        
        console.log('Redirecting to:', redirectUrl.toString());
        
        setTimeout(() => {
          window.location.href = redirectUrl.toString();
        }, 300);
        return;
      } else {
        // is region id - normal flow
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];
        regionNameCookie = selectedOption ? selectedOption.text : regionNameCookie;
        cookieValue = parseInt(value);
        
        console.log('Selected region:', { 
          id: cookieValue, 
          name: regionNameCookie,
          selectedIndex: regionSelect.selectedIndex,
          selectedOption: selectedOption
        });
      }
    }

    console.log('About to set cookies:', { cookieValue, regionNameCookie });

    RegionsModal.saveCookie(cookieValue, 'prothericsRegion');
    RegionsModal.saveCookie(regionNameCookie, 'prothericsRegionName');
    
    setTimeout(() => {
      RegionsModal.setRegionAttribute();
      RegionsModal.setRegionName();
      RegionsModal.hideModal();
      RegionsModal.refreshPage();
    }, 200);
  },
  // Add new method to set cookies for specific domains
  saveCookieForDomain: (cookieVal, name, targetDomain) => {
    let config = { 
      path: '/',
      sameSite: 'Lax'
    };

    // Extract the main domain from target domain
    const domainParts = targetDomain.split('.');
    if (domainParts.length > 2) {
      const mainDomain = '.' + domainParts.slice(-2).join('.');
      config.domain = mainDomain;
      console.log('Setting cross-domain cookie for target domain:', mainDomain);
    }

    if (cookieConfiguration[name].expires) {
      config.expires = cookieConfiguration[name].expires;
    }

    console.log('Setting cookie for target domain:', cookieConfiguration[name].name, '=', cookieVal, 'config:', config);
    Cookies.set(cookieConfiguration[name].name, cookieVal, config);
  },


  showModal: () => {
    RegionsModal.modal.classList.add(state.isOpen);
  },
  hideModal: () => {
    RegionsModal.modal.classList.remove(state.isOpen);
  },
  getCookie: name => {
    // Fix: Handle both string and array inputs
    const cookieName = Array.isArray(name) ? cookieConfiguration[name[0]].name : cookieConfiguration[name].name;
    const value = Cookies.get(cookieName);
    console.log('Getting cookie:', cookieName, '=', value);
    return value;
  },
  saveCookie: (cookieVal, name) => {
    let config = { 
      path: '/',
      sameSite: 'Lax'
    };

    // Always set domain for cross-subdomain access (both local and production)
    const hostname = window.location.hostname;
    const domainParts = hostname.split('.');
    
    if (domainParts.length > 2) {
      const mainDomain = '.' + domainParts.slice(-2).join('.');
      config.domain = mainDomain;
      console.log('Setting cross-domain cookie with domain:', mainDomain);
    }

    if (cookieConfiguration[name].expires) {
      config.expires = cookieConfiguration[name].expires;
    }

    console.log('Setting cookie:', cookieConfiguration[name].name, '=', cookieVal, 'config:', config);
    Cookies.set(cookieConfiguration[name].name, cookieVal, config);
    
    // Verify cookie was set
    const verification = Cookies.get(cookieConfiguration[name].name);
    console.log('Cookie verification:', cookieConfiguration[name].name, '=', verification);
    
    if (verification != cookieVal.toString()) {
      console.error('Cookie was not set correctly!', { expected: cookieVal, actual: verification });
    }
  },
  setRegionAttribute: () => {
    const regionId = RegionsModal.getCookie('prothericsRegion');

    if (regionId && regionId !== cookieConfiguration.defaultValue) {
      document.querySelector('body').setAttribute('region-id', regionId);
    }
  },
  setRegionName: () => {
    if (!RegionsModal.regionName) return;

    RegionsModal.regionName.textContent =
      RegionsModal.getCookie('prothericsRegionName') || cookieConfiguration['prothericsRegionName'].defaultValue;
  },
  refreshPage: () => {
    const regionId = Cookies.get('protherics_region');
    const normalizePath = path => path.replace(/\/+$/, '');
    const currentPath = normalizePath(window.location.pathname);
  
    const usRegionId = 2001;
    const usPrivacyUrl = '/privacy-policy-us';
    const globalPrivacyUrl = '/privacy-policy';
  
    console.log('refreshPage called', { regionId, currentPath, usPrivacyUrl, globalPrivacyUrl, usRegionId });
  
    if (currentPath === usPrivacyUrl && regionId !== usRegionId) {
      window.location.href = globalPrivacyUrl;
      return;
    }
  
    if (currentPath === globalPrivacyUrl && regionId === usRegionId) {
      window.location.href = usPrivacyUrl;
      return;
    }

    window.location.reload();
  }
};

RegionsModal.init();