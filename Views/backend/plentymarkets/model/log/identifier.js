// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/log/Identifier}

/**
 * The log data model defines the different data fields for logging and is
 * extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.log.Identifier', {

	extend: 'Ext.data.Model',

	fields: [

	// {block name="backend/Plentymarkets/model/log/Identifier/fields"}{/block}
	{
		name: 'identifier',
		type: 'string'
	}],

});
// {/block}
