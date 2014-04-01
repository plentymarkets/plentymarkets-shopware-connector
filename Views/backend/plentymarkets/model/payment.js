// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Payment}

/**
 * The payment data model defines the different data fields for payment lists and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Payment', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Payment/fields"}{/block}
	{
		name: 'id',
		type: 'integer'
	}, {
		name: 'name',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		api: {
			read: '{url action="getPaymentList"}'
        },

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
