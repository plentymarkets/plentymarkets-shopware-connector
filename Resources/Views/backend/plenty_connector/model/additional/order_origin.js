// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/model/additional/order_origin}

Ext.define('Shopware.apps.PlentyConnector.model.additional.OrderOrigin', {
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
