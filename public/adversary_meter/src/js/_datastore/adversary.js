'use strict'

export class Adversary {

    constructor(adversary) {
        this.id = adversary.id;
        this.name = adversary.name;
        this.firstContact = adversary.first_contact;
        this.lastContact = adversary.last_contact;
        this.ips = adversary.ips;
        this.level = adversary.aggressiveness;
    }
}
