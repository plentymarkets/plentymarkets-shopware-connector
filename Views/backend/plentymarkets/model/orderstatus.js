// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Orderstatus}

/**
 * The orderstatus data model defines the different data fields for order status values and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Orderstatus', {

	extend: 'Ext.data.Model',

	fields: [{
		name: 'status',
		type: 'float'
	}, {
		name: 'name',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
