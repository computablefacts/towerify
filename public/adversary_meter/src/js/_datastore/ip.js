'use strict'

export class IP {

    constructor(ip) {
        this.ip = ip.ip;
        this.firstContact = ip.first_contact;
        this.lastContact = ip.last_contact;
        this.countryCode = ip.country_code ? ip.country_code : '-';
        this.provider = ip.isp_name ? ip.isp_name : '-';
    }
}
