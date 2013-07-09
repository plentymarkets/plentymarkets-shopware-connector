// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/settings/Batch}
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
		model: 'Shopware.apps.Plentymarkets.model.Producer',
		name: 'getProducers',
		associationKey: 'producers'
	}]
});
// {/block}

