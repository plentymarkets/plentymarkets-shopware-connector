// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/mapping/transferobject}

Ext.define('Shopware.apps.PlentyConnector.model.mapping.TransferObject', {
    extend: 'Ext.data.Model',

    fields: [
        // {block name="backend/plentyconnector/model/mapping/transferobject/fields"}{/block}
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
