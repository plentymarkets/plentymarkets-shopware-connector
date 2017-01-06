// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/mapping/Main}

/**
 * The /mapping/main view initializes the seven log grid view tabs and loads the
 * mapping data. Each tab contains two columns, the "Shopware" column and the
 * "plentymarkets" column. It is extended by the Ext tab panel "Ext.tab.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.mapping.Main', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-mapping-main',

	title: '{s name=plentymarkets/view/mappingtabs/title}Mapping{/s}',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	isBuilt: false,

	/**
	 * Init the main detail component, add components
	 * 
	 * @return void
	 */
	initComponent: function()
	{
		var me = this;

		me.registerEvents();
		me.listeners = {
			activate: function()
			{
				me.fireEvent('load', me);
			}
		};

		me.callParent(arguments);
	},

	registerEvents: function () {
		this.addEvents('load');
		this.addEvents('reload');
		this.addEvents('save');
	}
});
// {/block}
