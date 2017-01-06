// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Row}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
 */
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Row', {

    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/Plentymarkets/model/mapping/Row/fields"}{/block}
        {
            name: 'identifier',
            type: 'string'
        },
        {
            name: 'name',
            type: 'string'
        },
        {
            name: 'adapterName',
            type: 'string'
        },
        {
            name: 'originIdentifier',
            type: 'string'
        },
        {
            name: 'originName',
            type: 'string'
        },
        {
            name: 'originAdapterName',
            type: 'string'
        },
        {
            name: 'objectType',
            type: 'string'
        }
    ],

    idProperty: 'identifier',

    proxy: {
        type: 'ajax',
        api: {
            update: '{url action=updateIdentities}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }

});
// {/block}
