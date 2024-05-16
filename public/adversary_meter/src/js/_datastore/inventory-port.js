'use strict'

export class InventoryPort {

    constructor(port) {
        this.id = port.uid;
        this.asset = port.asset;
        this.ip = port.ip;
        this.tags = port.tags
    }
}
