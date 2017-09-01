// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/settings}

Ext.define('Shopware.apps.PlentyConnector.model.Settings', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'rest_url',
            type: 'string'
        },
        {
            name: 'rest_username',
            type: 'string'
        },
        {
            name: 'rest_password',
            type: 'string'
        }
        // {block name="backend/plentyconnector/model/settings/fields"}{/block}
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
