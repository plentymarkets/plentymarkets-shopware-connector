// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Status}

/**
 * The status data model defines the different data fields for the mapping status and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Status', {

	extend: 'Ext.data.Model',

	fields: [{
		name: 'name',
		type: 'string'
	}, {
		name: 'open',
		type: 'integer'
	}],

	proxy: {
		type: 'ajax',

		api: {
			read: '{url  action="getMappingStatus"}'
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
