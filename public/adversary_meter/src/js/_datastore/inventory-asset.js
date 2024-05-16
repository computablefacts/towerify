'use strict'

import {apiCall} from "./api.js";

export class InventoryAsset {

    constructor(asset) {
        this.id = asset.uid;
        this.asset = asset.asset;
        this.ip = null;
        this.tags = asset.tags;
        this.is_watched = asset.status === "valid";
        this.tagsFromPorts = asset.tags_from_ports ? Object.values(asset.tags_from_ports) : [];
        this.is_range = this.tagsFromPorts.filter(tag => tag.is_range === true).length > 0;
    }

    update(asset) {
        this.id = asset.uid;
        this.asset = asset.asset;
        this.ip = null;
        this.tags = asset.tags;
        this.is_watched = asset.status === "valid";
    }

    async addTag(tag) {
        if (tag.match(/^[a-zA-Z0-9_/\-]*$/g)) {
            const url = `api/v2/facts/${this.id}/metadata`
            return await apiCall('POST', url, null, {
                type: 'Tag', key: tag, value: '1'
            })
        }
    }

    async removeTag(tagId) {
        const url = `api/v2/facts/${this.id}/metadata/${tagId}`
        return await apiCall('DELETE', url)
    }

    async startMonitoring() {
        const url = `api/v2/inventory/asset/${this.id}/monitoring/begin`;
        return await apiCall('POST', url);
    }

    async endMonitoring() {
        const url = `api/v2/inventory/asset/${this.id}/monitoring/end`;
        return await apiCall('POST', url);
    }

    async delete() {
        const url = `api/v2/adversary/assets/${this.id}`;
        return await apiCall('DELETE', url);
    }

    async restart(){
        const url = `api/v2/adversary/assets/restart/${this.id}`;
        return await apiCall('POST', url);
    }

    assetActions(action) {
        return action === 'start' ? this.startMonitoring() : this.endMonitoring();
    }
}
