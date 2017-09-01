// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/settings}

Ext.define('Shopware.apps.PlentyConnector.model.Settings', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/plentyconnector/model/settings/fields"}{/block}
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
