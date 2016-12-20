// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Status}

/**
 * The status store is used to load the status model for data mapping and
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle
 * model data like adding, getting and removing models in a defined store.
 */
Ext.define('Shopware.apps.Plentymarkets.store.Mapping', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model: 'Shopware.apps.Plentymarkets.model.Mapping',

    proxy: {
        type: 'ajax',

        api: {
            read: '{url  action="getMappings"}'
        },

        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
// {/block}
