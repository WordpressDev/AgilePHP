AgilePHP.IDE.FileExplorer.NewComponent = function() {

	var id = 'fe-new-component';
	var componentsRemote = new ComponentsRemote();
		componentsRemote.setCallback( function( response ) {

			var data = [];
			for( var i=0; i<response.apps.length; i++ ) {

				 data.push([ 
				             response.apps[i].id,
				             response.apps[i].appId,
				             response.apps[i].name,
				             response.apps[i].description,
				             response.apps[i].appType.name,
				             response.apps[i].currency.symbol + response.apps[i].cost,
				             response.apps[i].size
				 ]);
			}
			Ext.getCmp( id + '-new-component-grid' ).getStore().loadData( data );
		});
		componentsRemote.getApps();

	var store = new Ext.data.Store({
        proxy: new Ext.data.MemoryProxy( [] ),
        reader: new Ext.data.ArrayReader({}, [
                   {name: id + '-app-id'},
	               {name: id + '-app-appId'},
	               {name: id + '-app-name'},
	               {name: id + '-app-description'},
	               {name: id + '-app-type'},
	               {name: id + '-app-cost'},
	               {name: id + '-app-size'}
	          ])
	});

	var checkbox = new Ext.grid.CheckboxSelectionModel();
		checkbox.on( 'rowselect', function( selectionModel, rowIndex, record ) {

		  			alert( record );
	  	});

	var colModel = new Ext.grid.ColumnModel(
		    [
			  checkbox,
			  {
		        header: 'Id',
		        readOnly: true,
		        dataIndex: id + '-app-id',
		        hidden: true
		      },{
		        header: 'AppId',
		        dataIndex: id + '-app-appId',
		        width: 100
		      },{
		        header: 'Name',
		        dataIndex: id + '-app-name',
		        width: 150
		      },{
				header: 'Description',
		        dataIndex: id + '-app-description',
		        width: 200
			  }, {
				header: 'Type',
			    dataIndex: id + '-app-type',
			    width: 100
			  }, {
				header: 'Cost',
			    dataIndex: id + '-app-cost',
			    width: 100
			  }, {
				header: 'Size',
			    dataIndex: id + '-app-size',
			    width: 150
			  }]
		    );
		colModel.defaultSortable= true;

	var win = new AgilePHP.IDE.Window( id, 'btn-new-component', 'New Component', 550 );
		win.add( new Ext.grid.GridPanel({
			  		id: id + '-new-component-grid',
			        store: store,
			        viewConfig: {
			            forceFit: true
					},
					cm: colModel,
			        stripeRows: true,
			        stateId: 'grid',
			        bbar: new Ext.PagingToolbar({
			        	 id: id + '-new-component-pagingtoolbar',
			             pageSize: 6,
			             store: store,
			             displayInfo: true,
			             displayMsg: 'Displaying data {0} - {1} of {2}',
			             emptyMsg: 'No data to display'
			        })
				}));

	var ptoolbar = Ext.getCmp( id + '-new-component-pagingtoolbar' ); 
	ptoolbar.insertButton( 11, {
				id: id + '-new-component-pagingtoolbar-install',
				text: 'Install',
				iconCls: 'btn-list-add',
				handler: function() {

					var grid = Ext.getCmp( id + '-new-component-grid' );
					var data = grid.getSelectionModel().getSelected().json;

					componentsRemote.setCallback( function( response ) {

						new AgilePHP.IDE.Notification( '<b>Information</b>', 'Component is finished installing.')
			            var t = Ext.getCmp( 'ide-properties-components-treepanel' );
			            	t.getLoader().dataUrl = AgilePHP.getRequestBase() + '/FileExplorerController/getComponents/' + workspace + '/' + project;
			            	t.getRootNode().reload();

			            AgilePHP.IDE.FileExplorer.highlightedNode.reload();
					});
					var workspace = AgilePHP.IDE.FileExplorer.getWorkspace();
					var project = AgilePHP.IDE.FileExplorer.getSelectedProject();
					var projectRoot = workspace + '|' + project;

					componentsRemote.install( projectRoot, data[0], data[1] );
					win.close();
				}
	});
	ptoolbar.doLayout();

	return win;
};