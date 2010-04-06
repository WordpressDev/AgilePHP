Ext.QuickTips.init();
AgilePHP.IDE = {

	author: 'Jeremy Hahn',
	copyright: 'Make A Byte, inc.',
	version: '0.1a',
	licence: 'GNU General Public License v3',
	package: 'com.makeabyte.agilephp.ide',
	appName: 'AgilePHP Framework IDE',

	setDebug: function( val ) {

		AgilePHP.IDE.debug = (val) ? true : false;
		AgilePHP.setDebug( AgilePHP.IDE.debug );
	},

	logout: function() {

		// Destroy AgilePHP session
		var xhr = new AgilePHP.XHR();
			xhr.request( AgilePHP.getRequestBase() + '/ExtLoginController/logout' );

		// Destroy the workspace and load the login form
		AgilePHP.IDE.Workspace.destroy();
		setTimeout( 'AgilePHP.IDE.Login.show()', 500 );

		// Destroy all window instances
		Ext.WindowMgr.getBy( function( window ) {
			window.destroy();
			return true;
		}, this );
	},

	error: function( message ) {

		Ext.Msg.show({
		   minWidth: 200,
		   title: 'Error',
		   msg: message,
		   buttons: Ext.Msg.OK,
		   icon: Ext.MessageBox.ERROR
		});
	},

	info: function( message ) {

		Ext.Msg.show({
		   minWidth: 200,
		   title: 'Information',
		   msg: message,
		   buttons: Ext.Msg.OK,
		   icon: Ext.MessageBox.INFO
		});
	}
};

AgilePHP.IDE.Remoting = {

		load: function( clazz ) {

			AgilePHP.loadScript( AgilePHP.getRequestBase() + '/RemotingController/load/' + clazz );
		}
};