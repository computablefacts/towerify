'use strict'

import {apiCall, whoAmI} from "./api.js";
import {Adversary} from "./adversary.js";
import {InventoryAsset} from "./inventory-asset.js";
import {InventoryVulnerability} from "./inventory-vulnerability.js"
import {EventHoneypot} from "./event-honeypot.js";
import {IP} from "./ip.js";
import {HashObject} from "./hash.js";
import {HiddenAlert} from "./hidden-alert.js";

export function newDatastore() {

    const toaster = new com.computablefacts.blueprintjs.MinimalToaster(document.getElementById('toaster'));
    const self = {

        whoAmI: () => whoAmI(),

        discoverFromDomain: (domain) => apiCall('POST', 'api/inventory/assets/discover', null, {
            domain: domain
        }),

        discoverFromIp: (ip) => apiCall('POST', 'api/inventory/assets/discover/from/ip', null, {
            ip: ip
        }),

        saveAsset: (domain, type, watch = null) => apiCall('POST', 'api/inventory/assets', null, {
            asset: domain, type: type, watch: watch
        }).then(response => new InventoryAsset(response.asset)),

        getAssets: (valid = null, hours = null) => new com.computablefacts.promises.Memoize(3, () => {

            const params = {}

            if (valid !== null) {
                params.valid = valid;
            }
            if (hours !== null) {
                params.hours = hours;
            }

            return apiCall('GET', `api/inventory/assets`, params)
            .then(data => data.assets.map(asset => new InventoryAsset(asset)));
        }).promise(valid),

        getInfosFromAsset: (asset) => apiCall('GET', 'api/adversary/infos-from-asset/' + btoa(asset)),

        getScreenshot: (id) => apiCall('GET', 'api/inbox/screenshot/' + id),

        getAttackerIndex: () => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', `api/adversary/attacker-index`, {})
            .then(data => data.map(adversary => new Adversary(adversary)));
        }).promise(),

        getRecentEvents: (isAuto = true, isManual = true) => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', `api/adversary/recent-events`, {
                manual: isManual, auto: isAuto
            })
            .then(data => data.map(event => new EventHoneypot(event)));
        }).promise(),

        getBlacklistIps: (attackerProfileId = null) => new com.computablefacts.promises.Memoize(3, () => {
            const url = `api/adversary/blacklist-ips${attackerProfileId ? `/${attackerProfileId}` : ''}`;
            return apiCall('GET', url, {}).then(data => data.map(ip => new IP(ip)));
        }).promise(),

        getVulnerabilities: (attackerProfileId = null) => new com.computablefacts.promises.Memoize(1, () => {
            const url = `api/adversary/vulnerabilities${attackerProfileId ? `/${attackerProfileId}` : ''}`;
            return apiCall('GET', url, {})
            .then(data => {
                return data.map(alert => new InventoryVulnerability(alert))
            })
            .catch(error => {
                toaster.toast(`Error retrieving vulnerabilities: ${error}`);
                console.error(`Error retrieving vulnerabilities: ${error}`);
                throw error;
            });
        }).promise(),

        getVulnerabilities2: (asset) => new com.computablefacts.promises.Memoize(1, () => {
            const url = `api/adversary/vulnerabilities2/${btoa(asset)}`;
            return apiCall('GET', url, {})
            .then(data => {
                return data.map(alert => new InventoryVulnerability(alert))
            })
            .catch(error => {
                toaster.toast(`Error retrieving vulnerabilities: ${error}`);
                console.error(`Error retrieving vulnerabilities: ${error}`);
                throw error;
            });
        }).promise(),

        getAttackerActivity: (attackerProfileId) => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', 'api/adversary/activity/' + attackerProfileId, {})
            .then(data => {
                return {
                    firstDate: data.firstEvent,
                    top3: data.top3EventTypes,
                    events: data.events.map((event) => new EventHoneypot(event))
                }
            })
        }).promise(),

        getAttackerProfile: (attackerProfileId) => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', 'api/adversary/profile/' + attackerProfileId, {})
        }).promise(),

        getAttackerStats: (attackerProfileId) => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', 'api/adversary/profile/stats/' + attackerProfileId, {})
        }).promise(),

        getMostRecentEvent: (attackerProfileId) => new com.computablefacts.promises.Memoize(3, () => {
            return apiCall('GET', 'api/adversary/last/events' + (attackerProfileId ? `/${attackerProfileId}` : ''),
                {})
            .then(response => response.map(event => new EventHoneypot(event)))
        }).promise(),

        getTools: (attackerProfileId) => new com.computablefacts.promises.Memoize(3,
            () => apiCall('GET', `api/adversary/profile/tools/${attackerProfileId}`, {})).promise(),

        calculateCompetencyScores: (attackerProfileId) => new com.computablefacts.promises.Memoize(3,
            () => apiCall('GET', `api/adversary/profile/competency/${attackerProfileId}`, {})).promise(),

        getHoneypots: () => new com.computablefacts.promises.Memoize(3,
            () => apiCall('GET', `api/adversary/last/honeypots`, {})).promise(),

        getHoneypotStats: (honeypot, days) => new com.computablefacts.promises.Memoize(3,
            () => apiCall('GET', `api/adversary/honeypots/stats/${honeypot}`, {days: days})).promise(),

        getAlertStats: () => new com.computablefacts.promises.Memoize(3,
            () => apiCall('GET', `api/adversary/alerts/stats`)).promise(),

        getHoneypotsStatus: () => apiCall('GET', `api/adversary/honeypots/status`),

        postHoneypots: (honeypots) => apiCall('POST', `api/adversary/honeypots`, null, {
            honeypots: honeypots
        }),

        setHoneypotsNextStep: () => apiCall('POST', `api/adversary/honeypots/set-next-step`, null),

        getAssetTags: () => apiCall('GET', `api/adversary/assets/tags`),

        getHashes: () => apiCall('GET', `api/adversary/hashes`).then(
            response => response.map(hash => new HashObject(hash))),

        postHash: (tag) => apiCall('POST', `api/adversary/hashes`, null, {tag: tag}).then(
            response => new HashObject(response)),

        deleteHash: (id) => apiCall('DELETE', `api/adversary/hashes/${id}`),

        postHiddenAlert: (payload) => apiCall('POST', 'api/adversary/hidden-alerts', null, payload),

        deleteHiddenAlert: (id) => apiCall('DELETE', 'api/adversary/hidden-alerts/' + id),

        toast: function (msg) {
            toaster.toast(msg, 'primary');
        },

        toastSuccess: function (msg) {
            toaster.toast(msg, 'success');
        },

        toastDanger: function (msg) {
            toaster.toast(msg, 'danger');
        },

        toastWarning: function (msg) {
            toaster.toast(msg, 'warning');
        },
    };
    return self;
}
