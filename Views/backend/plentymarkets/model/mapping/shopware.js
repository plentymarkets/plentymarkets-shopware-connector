// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/mapping/Shopware}

/**
 * The shopware data model defines the different data fields for data mapping on the side of shopware and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.mapping.Shopware', {
	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/plentymarkets/model/mapping/Shopware/fields"}{/block}
	{
		name: 'id',
		type: 'string'
	}, {
		name: 'groupName',
		type: 'string'
	}, {
		name: 'name',
		type: 'string'
	}, {
		name: 'position',
		type: 'string'
	}, {
		name: 'plentyName',
		type: 'string'
	}, {
		name: 'plentyId',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		api: {
			create: '{url action="saveMapping"}',
			update: '{url action="saveMapping"}'
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
