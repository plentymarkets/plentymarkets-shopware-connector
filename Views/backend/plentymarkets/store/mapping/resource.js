// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Resource}

/**
 * The resource store is used to autoload data fields like currency or country and
 * is extended by the Ext data store "Ext.data.Store". With Ext stores you can handle
 * model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.mapping.Resource', {
	extend: 'Ext.data.Store',

	autoLoad: true,
	remoteFilter: false,
	remoteSort: false,

	pageSize: 100,

	fields: [{
		name: 'id',
		type: 'string'
	}, {
		name: 'name',
		type: 'string'
	}],

	data: [{
		id: "Currency",
		name: "Währungen"
	}, {
		id: "MeasureUnit",
		name: "Einheiten"
	}, {
		id: "MethodOfPayment",
		name: "Zahlungsarten"
	}, {
		id: "Vat",
		name: "Steuern"
	}, {
		id: "ShippingProfile",
		name: "Versandarten"
	}, {
		id: "Country",
		name: "Länder"
	}]
});
// {/block}
