'use strict'

export class EventHoneypot {

    constructor(event) {
        this.id = event.id;
        this.internalName = event.internal_name && event.internal_name !== 'shadow-broker' ? event.internal_name : '-';
        this.event = event.event;
        this.uid = event.uid;
        this.human = event.human == 1;
        this.endpoint = event.endpoint;
        this.timestamp = event.timestamp;
        this.requestUri = event.request_uri;
        this.userAgent = event.user_agent;
        this.ip = event.ip;
        this.detail = event.details;
        this.targeted = event.targeted == 1;
        this.feedName = event.feed_name;
        this.attackerId = event.attacker_id;
    }

    get honeypot() {
        let endIndex = this.feedName.indexOf('-access.');
        return endIndex !== -1 ? this.feedName.substring(6, endIndex) : null;
    }

    get timestampFormatted() {
        return this.timestamp.replace('T', ' ').substring(0, this.timestamp.lastIndexOf(':')) + ' UTC';
    }
}
