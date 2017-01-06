// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Settings}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Settings', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Settings/fields"}{/block}
	{
		name: 'PlentymarketsVersion',
		type: 'string'
	}, {
		name: 'ApiUrl',
		type: 'string'
	}, {
		name: 'ApiUsername',
		type: 'string'
	}, {
		name: 'ApiPassword',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

        api: {
        	read:   '{url action=readSettings}',
            update: '{url action=saveSettings}',
            delete: '{url action=deleteSettings}'
        },

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
