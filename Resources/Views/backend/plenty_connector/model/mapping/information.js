// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/mapping/information}

Ext.define('Shopware.apps.PlentyConnector.model.mapping.Information', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/plentyconnector/model/mapping/information/fields"}{/block}
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
        timeout: 300000,
        reader: {
            type: 'json',
            root: 'data',
            messageProperty: 'message'
        }
    }
});

// {/block}
