// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/settings/ItemCleanupAction}

/**
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.settings.ItemCleanupAction', {
	extend: 'Ext.data.Store',

	autoLoad: true,
	remoteFilter: false,
	remoteSort: false,

	pageSize: 100,

	fields: [{
		name: 'id',
		type: 'integer'
	}, {
		name: 'name',
		type: 'string'
	}],

	data: [{
		id: 1,
		name: "Artikel deaktivieren"
	}, {
		id: 2,
		name: "Artikel unwiederbringlich löschen"
	}]
});
// {/block}
