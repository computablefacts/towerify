'use strict'

const httpClient = new com.computablefacts.platform.HttpClient();

// try to autodetect cf-ui base URL first
httpClient.init();

// !!! BEGIN TOWERIFY-SPECIFIC !!!
const urlParams = new URLSearchParams(window?.location?.search);
httpClient.setBaseUrl(urlParams.get('api_url'));
httpClient.setToken(urlParams.get('api_token'));
// !!! END TOWERIFY-SPECIFIC !!!

if (httpClient.getBaseUrl() === '') {
    // If not found, use hardcoded base URL
    httpClient.init(conf.API_BASE_URL);
}

export function urlStartDiscussion(vulnId) {
    return httpClient.getBaseUrl() + `/api/v2/adversary/start-discussion/${vulnId}?api_token=${httpClient.getToken()}`;
}

export async function whoAmI() {
    return httpClient.whoAmI().then(resp => resp.data);
}

export async function apiCall(method, url, params = {}, body = null) {

    let fullUrl = httpClient.getBaseUrl() + "/" + url;

    if (method.toUpperCase() === "GET" && Object.keys(params).length > 0) {
        const queryParams = new URLSearchParams(params).toString();
        fullUrl += "?" + queryParams;
    }

    const headers = {
        'Content-Type': 'application/json', 'Authorization': `Bearer ${httpClient.getToken()}`,
    };

    const options = {
        method: method, headers: headers, body: body ? JSON.stringify(body) : null,
    };

    const response = await fetch(fullUrl, options);

    if (!response.ok) {
        const error = await response.json()
        throw new Error(`${await error.error}`);
    }

    // Check if the response is json before parsing it as JSON
    const contentType = response.headers.get("content-type");

    if (contentType && contentType.includes("application/json")) {
        return await response.json();
    } else {
        return null;
    }
}
