// {namespace name=backend/plentyconnector/model}
// {block name=backend/plentyconnector/model/settings}

Ext.define('Shopware.apps.PlentyConnector.model.Settings', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/plentyconnector/model/settings/fields"}{/block}
        {
            name: 'PlentymarketsVersion',
            type: 'string'
        }, {
            name: 'ApiUrl',
            type: 'string'
        }, {
            name: 'ApiUsername',
            type: 'string'
        }, {
            name: 'ApiPassword',
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
