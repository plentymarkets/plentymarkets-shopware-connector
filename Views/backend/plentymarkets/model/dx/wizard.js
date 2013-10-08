// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/dx/Wizard}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.dx.Wizard', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/dx/Wizard/fields"}{/block}
	{
		name: 'isActive',
		type: 'boolean'
	}, {
		name: 'mayActivate',
		type: 'boolean'
	}

	],

	proxy: {
		type: 'ajax',

		api: {
			read: '{url action=getDxWizard}',
			update: '{url action=setDxWizard}'
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
