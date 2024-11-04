'use strict'

import {TabDirectoryAdversaries} from "./_adversary-meter/tab-directory-adversaries.js";
import {newDatastore} from "./_datastore/datastore.js";
import {TabActivityAdversaries} from "./_adversary-activity/tab-activity-adversaries.js";
import {TabIPBlacklist} from "./_ip-blacklist/tab-ip-blacklist.js";
import {TabAssets} from "./_assets/tab-assets.js";
import {TabVulnerabilities} from "./_vulnerabilities/tab-vulnerabilities.js";
import {TabImpactAnalysis} from "./profile/tab-impact-analysis.js";
import {TabAdversaryIp} from "./profile/tab-adversary-ip.js";
import {TabActivity} from "./profile/tab-activity.js";
import {TabPreview} from "./_preview/tab-preview.js";
import {TabProfile} from "./profile/tab-profile.js";
import {TabAssetsImporter} from "./_assets/tab-assets-importer.js";
import {TabHoneypots} from "./_honeypots/tab-honeypots.js";
import {TabDelegation} from "./_service-provider-delegation/tab-service-provider-delegation.js";

export class App {

  static PREVIEW = i18next.t('HONEYPOTS');
  static DIRECTORY_ADVERSARIES = i18next.t('ANNUAIRE DES ATTAQUANTS');
  static ACTIVITY_ADVERSARIES = i18next.t('ACTIVITÉ DES ATTAQUANTS');
  static IP_BLACKLIST = i18next.t('IP À BLACKLISTER');
  static ASSETS = i18next.t('MES ACTIFS');
  static VULNERABILITIES = i18next.t('MES VULNÉRABILITÉS');
  static DELEGATION = i18next.t('DÉLÉGATION PRESTATAIRE');
  static PROFILE = i18next.t("PROFILE DE L'ATTAQUANT");
  static IMPACT_ANALYSIS = i18next.t("ANALYSE D'IMPACT");
  static ADVERSARY_ACTIVITY = i18next.t("ACTIVITÉ DE L'ATTAQUANT");
  static IP_ADVERSARY = i18next.t("IP UTILISÉES PAR L'ATTAQUANT");

  constructor() {
    this.assets_ = []
    this.honeypotsStatus_ = null;
    this.user_ = null;
    this.datastore_ = newDatastore()
  }

  async whoami() {
    try {
      this.user_ = await this.datastore_.whoAmI();
    } catch (e) {
      this.datastore_.toastDanger(`${e}`);
    }
  }

  async getHoneypots() {
    try {
      this.honeypotsStatus_ = await this.datastore_.getHoneypotsStatus();
    } catch (e) {
      this.datastore_.toastDanger(`${e}`);
    }
  }

  async getAssets() {
    try {
      this.assets_ = await this.datastore_.getAssets();
    } catch (e) {
      this.datastore_.toastDanger(`${e}`);
    }
  }

  /**
   * Display the app.
   *
   * @param {string} elementId the parent element id.
   */
  async init(elementId) {

    await this.whoami();
    await this.getHoneypots();

    const container = document.getElementById(elementId);
    container.innerHTML = `
        <style>
            #trial-status {
                padding: 0.5rem;
                font-weight: bold;
                background-color: #FFEBEB;
                border: 1px solid #FF5F52;
                align-items: center;
            }
            .bp4-tab[aria-selected=true], .bp4-tab:not([aria-disabled=true]):hover {
                color: black;
            }
            .bp4-tab {
                color: black;
                font-weight: bold;
                outline: none;
            }
            .bp4-tab-indicator {
                background-color: black!important;
            }
            .bp4-control-indicator {
                outline: none !important;
            }
            #app-header {
                height: 70px;
            }
            #app-menu, #profile-menu{
                height: 35px;
            }
            #app-body {
                box-shadow: 0 5px 5px #E3E4EC;
                height: 0;
            }
        </style>
        <div id="app-header" class="d-flex flex-column justify-content-end mb-1">
            <div class="d-flex py-2 mt-auto d-none flex-grow-1 justify-content-center" id="trial-status">
              ${i18next.t('Votre période d\'essai a expiré. Rendez-vous')}&nbsp;
              <a href="${this.user_ && this.user_.link_subscribe ? this.user_.link_subscribe
      : '#'}" target="_blank">${i18next.t('ici')}</a>&nbsp;
              ${i18next.t('pour souscrire à un abonnement.')}
            </div>
            <div class="d-flex py-2 mt-auto d-none" id="back-button">
                <div role="button" class="d-flex">
                  <i class="fa fa-chevron-left orange my-auto"></i>
                  <div class="fw-bold orange my-auto ms-2">${i18next.t('Retourner à l\'annuaire de l\'attaquant')}</div>
                </div>
            </div>
        </div>
        <div id="main-app" class="flex-grow-1 d-flex flex-column mb-3">
            <div class="d-flex justify-content-between">
                <div id="app-menu" class="d-flex d-none">
                    <!-- FILLED DYNAMICALLY -->
                </div>
                <button id="add-assets" type="button" class="d-none btn btn-primary ms-auto rounded-0 mb-1">${i18next.t(
      '+ Ajouter des actifs')}</button>
            </div>
            <div id="main-loader" class="my-auto flex-grow-1"></div>
            <div id="app-body" class="d-none d-flex flex-column flex-grow-1 bg-white border">
              <!-- FILLED DYNAMICALLY -->
            </div>
        </div>
        <div id="profile" class="d-flex flex-grow-1 flex-column d-none">
            <div id="profile-menu" class="d-flex">
              <!-- FILLED DYNAMICALLY -->
            </div>
            <div id="profile-body" class="d-flex flex-column flex-grow-1 mb-3">
              <!-- FILLED DYNAMICALLY -->
            </div>
        </div>
        <div id="honeypot" class="d-flex flex-grow-1 flex-column d-none">
             <!-- FILLED DYNAMICALLY -->
        </div>
    `;

    const main = container.querySelector('#main-app')
    const header = container.querySelector('#app-header')
    const profile = container.querySelector('#profile')
    const honeypot = container.querySelector('#honeypot')
    const menuContainer = container.querySelector('#app-menu')
    const menu = new com.computablefacts.blueprintjs.MinimalTabs(container.querySelector('#app-menu'));
    const profileMenu = new com.computablefacts.blueprintjs.MinimalTabs(container.querySelector('#profile-menu'));
    const body = container.querySelector('#app-body');
    const profileBody = container.querySelector('#profile-body');
    const backButton = container.querySelector('#back-button');
    const loaderContainer = container.querySelector('#main-loader');
    const loader = new com.computablefacts.blueprintjs.MinimalSpinner(loaderContainer);
    const button = container.querySelector('#add-assets')
    const url = new URL(window.location.href);
    const alertType = url.searchParams.get('type');
    const tab = url.searchParams.get('tab');
    let widget = null;
    let level = alertType && alertType !== '' ? alertType : null;
    let asset = null;
    let profileName = null;
    let profileId = null;

    if (this.user_ && this.user_.trial_status === 'expired') {
      const trialStatus = container.querySelector('#trial-status');
      trialStatus.classList.remove('d-none');
    }

    menu.addTab(App.PREVIEW, body);
    menu.addTab(App.DIRECTORY_ADVERSARIES, body);
    menu.addTab(App.ACTIVITY_ADVERSARIES, body);
    menu.addTab(App.IP_BLACKLIST, body);
    menu.addTab(App.ASSETS, body);
    menu.addTab(App.VULNERABILITIES, body);
    menu.addTab(App.DELEGATION, body);

    menu.onSelectionChange((tabName, tabBody) => {
      if (widget !== null) {
        widget.destroy();
        widget = null;
        button.classList.add('d-none');
      }
      if (tabName === App.DIRECTORY_ADVERSARIES) {
        widget = new TabDirectoryAdversaries(tabBody, this.datastore_);
        widget.onShowProfile((params) => {
          profileName = params.name;
          profileId = params.id;
          profile.classList.remove('d-none');
          main.classList.add('d-none');
          container.classList.add('very-light-orange');
          backButton.classList.remove('d-none');
          profileMenu.selectTab(App.PROFILE);
        })
        widget.onShowBlacklist((params) => {
          profileName = params.name;
          profileId = params.id;
          profile.classList.remove('d-none');
          main.classList.add('d-none');
          container.classList.add('very-light-orange');
          backButton.classList.remove('d-none');
          profileMenu.selectTab(App.IP_ADVERSARY);
        })
      } else if (tabName === App.ACTIVITY_ADVERSARIES) {
        widget = new TabActivityAdversaries(tabBody, this.datastore_);
        widget.onShowProfile((params) => {
          profileName = params.name;
          profileId = params.id;
          profile.classList.remove('d-none');
          main.classList.add('d-none');
          container.classList.add('very-light-orange');
          backButton.classList.remove('d-none');
          profileMenu.selectTab(App.PROFILE);
        })
      } else if (tabName === App.IP_BLACKLIST) {
        widget = new TabIPBlacklist(tabBody, this.datastore_);
      } else if (tabName === App.ASSETS) {
        widget = new TabAssets(tabBody, this.datastore_)
        widget.onFilterClick((data) => {
          widget.destroy();
          level = data.level;
          asset = data.asset;
          menu.selectTab(App.VULNERABILITIES)
        })
        widget.onRadioChange((data) => {
          this.datastore_.getAssets(null, data.hours).then((data) => {
            widget.assets = data;
          })
        })
        widget.onDelete(() => {
          this.getAssets().then(() => widget.assets = this.assets_);
        })
        widget.assets = this.assets_;
        button.classList.remove('d-none');
      } else if (tabName === App.VULNERABILITIES) {
        widget = new TabVulnerabilities(tabBody, this.datastore_, level, asset)
        level = null;
        asset = null;
      } else if (tabName === App.PREVIEW) {
        widget = new TabPreview(tabBody, this.datastore_);
        widget.onShowEvents(() => {
          widget.destroy();
          menu.selectTab(App.ACTIVITY_ADVERSARIES);
        })
        widget.onConfigureHoneypots((honeypots) => {
          openHoneypot.bind(this)(honeypots, 'inactive');
        });
      } else if (tabName === App.DELEGATION) {
        widget = new TabDelegation(tabBody, this.datastore_);
      }
    })

    button.onclick = () => {
      widget.destroy();
      widget = null;
      initImporter.bind(this)()
    }

    profileMenu.addTab(App.PROFILE, profileBody);
    profileMenu.addTab(App.IMPACT_ANALYSIS, profileBody);
    profileMenu.addTab(App.ADVERSARY_ACTIVITY, profileBody);
    profileMenu.addTab(App.IP_ADVERSARY, profileBody);

    let widgetProfile = null

    profileMenu.onSelectionChange((tabName, tabBody) => {
      if (widgetProfile !== null) {
        widgetProfile.destroy();
        widgetProfile = null;
      }
      if (tabName === App.IMPACT_ANALYSIS) {
        widgetProfile = new TabImpactAnalysis(profileBody, this.datastore_, profileName, profileId);
      } else if (tabName === App.IP_ADVERSARY) {
        widgetProfile = new TabAdversaryIp(profileBody, this.datastore_, profileName, profileId);
      } else if (tabName === App.ADVERSARY_ACTIVITY) {
        widgetProfile = new TabActivity(profileBody, this.datastore_, profileName, profileId);
      } else if (tabName === App.PROFILE) {
        widgetProfile = new TabProfile(profileBody, this.datastore_, profileName, profileId);
      }
    });

    backButton.onclick = () => {
      profile.classList.add('d-none');
      main.classList.remove('d-none');
      container.classList.remove('very-light-orange');
      backButton.classList.add('d-none');
      if (widgetProfile !== null) {
        widgetProfile.destroy();
        widgetProfile = null;
      }
    }

    function initImporter() {
      widget = new TabAssetsImporter(body, this.datastore_, this.assets_)
      button.classList.add('d-none');
      widget.onBackClick(async () => {
        loader.render()
        loaderContainer.classList.toggle('d-none')
        body.classList.toggle('d-none');
        await this.getAssets()
        loader.destroy()
        loaderContainer.classList.toggle('d-none')
        body.classList.toggle('d-none');
        menu.selectTab(App.ASSETS);
        button.classList.remove('d-none');
      })
    }

    function openHoneypot(honeypots, status) {

      loader.destroy()
      main.classList.add('d-none')
      header.classList.add('d-none');
      honeypot.classList.remove('d-none')

      widget = new TabHoneypots(honeypot, this.datastore_, this.honeypotsStatus_.current_user, status, honeypots)
      widget.onCancelConfiguration(() => {
        this.getAssets().then(() => {
          loader.destroy();
          header.classList.remove('d-none');
          loaderContainer.classList.add('d-none');
          menuContainer.classList.remove('d-none');
          body.classList.remove('d-none');
          honeypot.classList.add('d-none');
          main.classList.remove('d-none')

          if (this.assets_.length) {
            menu.selectTab(App.PREVIEW);
          } else {
            initImporter.bind(this)()
          }
        });
      });
    }

    if (!conf.SKIP_HONEYPOT && (this.honeypotsStatus_ && this.honeypotsStatus_.integration_status
      !== "setup_complete")) {
      openHoneypot.bind(this)(this.honeypotsStatus_.honeypots, this.honeypotsStatus_.integration_status);
    } else {
      await this.getAssets();
      loader.destroy()
      header.classList.remove('d-none');
      loaderContainer.classList.add('d-none');
      menuContainer.classList.remove('d-none');
      body.classList.remove('d-none');
      honeypot.classList.add('d-none')
      if (this.assets_.length) {
        if (tab) {
          if (tab === 'setup_honeypots') {
            if (this.honeypotsStatus_ && this.honeypotsStatus_.integration_status !== "setup_complete") {
              openHoneypot.bind(this)(this.honeypotsStatus_.honeypots, this.honeypotsStatus_.integration_status);
            } else {
              menu.selectTab(App.PREVIEW);
            }
          } else if (tab === 'honeypots') {
            menu.selectTab(App.PREVIEW);
          } else if (tab === 'assets') {
            menu.selectTab(App.ASSETS);
          } else if (tab === 'attackers') {
            menu.selectTab(App.DIRECTORY_ADVERSARIES);
          } else if (tab === 'blacklist') {
            menu.selectTab(App.IP_BLACKLIST);
          } else if (tab === 'delegation') {
            menu.selectTab(App.DELEGATION);
          } else {
            menu.selectTab(App.VULNERABILITIES);
          }
        } else {
          if (level) {
            menu.selectTab(App.VULNERABILITIES);
          } else if (conf.SKIP_HONEYPOT) {
            menu.selectTab(App.VULNERABILITIES);
          } else {
            menu.selectTab(App.PREVIEW);
          }
        }
      } else {
        initImporter.bind(this)()
      }
    }
  }
}
