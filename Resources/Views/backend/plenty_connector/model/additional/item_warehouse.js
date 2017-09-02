// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/additional/item_warehouse}

Ext.define('Shopware.apps.PlentyConnector.model.additional.ItemWarehouse', {
    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'id',
            type: 'integer'
        },
        {
            name: 'name',
            type: 'string'
        }
    ],

    idProperty: 'id'
});

// {/block}
