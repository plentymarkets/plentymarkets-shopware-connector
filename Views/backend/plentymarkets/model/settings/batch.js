// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/settings/Batch}

/**
 * The batch data model associates a model with a function name and an association key and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.settings.Batch', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/settings/Batch/fields"}{/block}
	'id'],

	associations: [{
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Warehouse',
		name: 'getWarehouses',
		associationKey: 'warehouses'
	}, {
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Referrer',
		name: 'getOrderReferrer',
		associationKey: 'orderReferrer'
	}, {
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Orderstatus',
		name: 'getOrderStatus',
		associationKey: 'orderStatus'
	}, {
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Multishop',
		name: 'getMultishops',
		associationKey: 'multishops'
	}, {
        type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Payment',
		name: 'getPayments',
		associationKey: 'payments'
	}, {
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.Producer',
		name: 'getProducers',
		associationKey: 'producers'
	}]
});
// {/block}

