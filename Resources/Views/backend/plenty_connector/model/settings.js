// {namespace name=backend/plentyconnector/model}
// {block name=backend/plentyconnector/model/settings}

Ext.define('Shopware.apps.PlentyConnector.model.Settings', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/plentyconnector/model/settings/fields"}{/block}
        {
            name: '{s name=plentyconnector/model/settings/fields/apiurl}{/s}',
            type: 'string'
        },
        {
            name: '{s name=plentyconnector/model/settings/fields/apiusername}{/s}',
            type: 'string'
        },
        {
            name: '{s name=plentyconnector/model/settings/fields/apipassword}{/s}',
            type: 'string'
        }
    ],

    proxy: {
        type: 'ajax',

        api: {
            read: '{url action=readSettings}',
            update: '{url action=saveSettings}',
            delete: '{url action=deleteSettings}'
        },

        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
// {/block}
