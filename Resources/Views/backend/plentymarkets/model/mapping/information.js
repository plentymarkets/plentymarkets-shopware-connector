// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Information}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
 */
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Information', {

    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/Plentymarkets/model/mapping/Information/fields"}{/block}
        {
            name: 'originAdapterName',
            type: 'string'
        },
        {
            name: 'originTransferObjects',
            type: 'auto'
        },
        {
            name: 'destinationAdapterName',
            type: 'string'
        },
        {
            name: 'destinationTransferObjects',
            type: 'auto'
        },
        {
            name: 'objectType',
            type: 'string'
        }
    ],

    proxy: {
        type: 'ajax',
        api: {
            read: '{url action=getMappingInformation}'
        },
        timeout: 45000,
        reader: {
            type: 'json',
            root: 'data',
            messageProperty: 'message'
        }
    }
});
// {/block}
