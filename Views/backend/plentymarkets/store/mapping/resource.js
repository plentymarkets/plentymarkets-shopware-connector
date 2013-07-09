// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/mapping/Resource}
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
		id: "VAT",
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
