// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/TransferObject}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
 */
Ext.define('Shopware.apps.Plentymarkets.model.mapping.TransferObject', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/Plentymarkets/model/mapping/TransferObject/fields"}{/block}
        {
            name: 'identifier',
            type: 'string'
        },
        {
            name: 'name',
            type: 'string'
        }
    ]
});
// {/block}
