<?php

// Fetch current user
$user = wp_get_current_user();

?><!DOCTYPE html>
<html>
<head>
	<title><?php echo get_bloginfo( 'name' ) ?> &#8212; WordPress</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
	<link rel="icon" href="<?php echo get_site_icon_url(); ?>" sizes="192x192" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
	<?php vuetifused_header_content_extracted(); ?>
	<style>
	[v-cloak] > * {
		display:none;
	}
	[v-cloak]::before {
		display: block;
		position: relative;
		left: 0%;
		top: 0%;
		max-width: 1000px;
		margin:auto;
		padding-bottom: 10em;
	}
	</style>
</head>
<body>
<div id="app" v-cloak>
<v-app>
<v-app-bar app dark style="left:0px" color="primary">
	<v-toolbar-title><?php echo get_bloginfo( 'name' ) ?></v-toolbar-title>
	<v-spacer></v-spacer>
	<v-tooltip bottom>
	<template v-slot:activator="{ on, attrs }">
		<v-btn v-bind="attrs" v-on="on" icon href="<?php echo home_url(); ?>">
			<v-icon>mdi-eye</v-icon>
		</v-btn>
	</template>
	<span>View homepage</span>
	</v-tooltip>
	<v-tooltip bottom>
	<template v-slot:activator="{ on, attrs }">
		<v-btn v-bind="attrs" v-on="on" icon href="/wp-admin/admin-ajax.php?action=vuetifused_ajax&command=disable">
			<v-icon>mdi-exit-to-app</v-icon>
		</v-btn>
	</template>
	<span>Exit to default /wp-admin/</span>
	</v-tooltip>
</v-app-bar>
<v-navigation-drawer app clipped>
<v-list dense>
	<v-subheader class="text-uppercase">Management</v-subheader>
	<v-list-item-group v-model="page" color="primary">
		<v-list-item value="content">
		<v-list-item-icon>
			<v-icon>mdi-file-edit</v-icon>
		</v-list-item-icon>
		<v-list-item-content>
			<v-list-item-title>Content</v-list-item-title>
		</v-list-item-content>
		</v-list-item>
		<v-list-item value="themes">
		<v-list-item-icon>
			<v-icon>mdi-home-edit</v-icon>
		</v-list-item-icon>
		<v-list-item-content>
			<v-list-item-title>Themes</v-list-item-title>
		</v-list-item-content>
		</v-list-item>
		<v-list-item value="plugins">
		<v-list-item-icon>
			<v-icon>mdi-power-plug</v-icon>
		</v-list-item-icon>
		<v-list-item-content>
			<v-list-item-title>Plugins</v-list-item-title>
		</v-list-item-content>
		</v-list-item>
	</v-list-item-group>
	</v-list>
	<template v-slot:append>
	<v-menu offset-y top>
	<template v-slot:activator="{ on }">
		<v-list>
		<v-list-item link v-on="on">
			<v-list-item-avatar>
				<v-img :src="gravatar"></v-img>
			</v-list-item-avatar>
			<v-list-item-content>
				<v-list-item-title>{{ current_user_display_name }}</v-list-item-title>
			</v-list-item-content>
			<v-list-item-icon>
				<v-icon>mdi-chevron-up</v-icon>
			</v-list-item-icon>
		</v-list-item>
		</v-list>
	</template>
	<v-list dense>
		<v-list-item link @click="signOut()">
		<v-list-item-icon>
			<v-icon>mdi-logout</v-icon>
		</v-list-item-icon>
		<v-list-item-content>
			<v-list-item-title>Log Out</v-list-item-title>
		</v-list-item-content>
		</v-list-item>
	</v-list>
	</v-menu>
	</template>
</v-navigation-drawer>
<v-main>
	<div v-if="page == 'content'">
	<v-toolbar class="mb-2">
	<v-select
		:items="[
			{ text: 'Pages', value: 'page' },
			{ text: 'Posts', value: 'post' },
		]"
		label="Select"
		v-model="content.selected"
		persistent-hint
		single-line
		@change="options.page = 1; fetchContent()"
		style="max-width: 120px"
		class="mt-3"
		></v-select>
	<v-spacer></v-spacer>
	<v-toolbar-items>
	<v-btn :href="newContentLink" depressed color="white">
		<v-icon class="mr-1">mdi-plus-box</v-icon> New {{ this.content.selected }}
	</v-btn>
	</v-toolbar-items>
	</v-toolbar>
	<v-data-table 
		:items="content.results"
		:options.sync="options"
		:server-items-length="content.count"
		:items-per-page="10"
		:footer-props="{ itemsPerPageOptions: [100] }"
		:headers="[
			{ text: 'Name', value: 'post_title' },
			{ text: 'Date', value: 'post_date', width: '200px' },
			{ text: 'Status', value: 'post_status' },
			{ text: '', value: 'actions', width: '95px' }
		]"
		
	>
	<template v-slot:item.post_title="{ item }">
		<v-row>
			<v-col style="max-width:150px"><v-img :src="item.thumbnail" width="150px" v-if="item.thumbnail"></v-img></v-col>
			<v-col class="align-self-center my-1">
				<span class="body-1">{{ item.post_title }}</span><br />
				<span class="caption"><v-btn small depressed><v-icon small class="mr-1">mdi-content-copy</v-icon> {{ item.post_name }}</span></v-btn>
			</v-col>
		</v-row>
	</template>
	<template v-slot:item.actions="{ item }">
		<v-btn icon dense small :href=`/?p=${item.ID}`><v-icon>mdi-eye</v-icon></v-btn>
		<v-btn icon dense small :href=`/wp-admin/post.php?post=${item.ID}&action=edit`><v-icon>mdi-pencil-outline</v-icon></v-btn>
	</template>
	</v-data-table>
	</div>
	<v-data-table 
		:items="themes"
		:footer-props="{ itemsPerPageOptions: [{'text':'All','value':-1}] }"
		:headers="[
			{ text: 'Name', value: 'name' },
			{ text: 'Version', value: 'version', width: '120px' },
			{ text: 'Status', value: 'status', width: '90px' },
			{ text: '', value: 'actions', width: '35px', sortable: false }
		]"
		v-if="page == 'themes'"
	>
	<template v-slot:item.name="{ item }">
		<v-row>
			<v-col style="max-width:150px"><v-img :src="item.screenshot" width="150px" v-if="item.screenshot"></v-img></v-col>
			<v-col class="align-self-center my-1">
				<span class="body-1">{{ item.name }}</span><br />
				<span class="caption"><v-btn small depressed><v-icon small class="mr-1">mdi-content-copy</v-icon> {{ item.slug }}</v-btn></span>
			</v-col>
		</v-row>
	</template>
	<template v-slot:item.status="{ item }">
	<v-switch hide-details v-model="item.status" @change="activateTheme( item.slug )" :disabled="item.status == true" class="ma-0"></v-switch>
	</template>
	<template v-slot:item.actions="{ item }">
		<v-btn icon small @click="deleteTheme( item.slug )" v-if="item.status == false">
			<v-icon>mdi-delete</v-icon>
		</v-btn>
	</template>
	</v-data-table>
	<v-data-table 
		:items="plugins"
		:footer-props="{ itemsPerPageOptions: [{'text':'All','value':-1}] }"
		:headers="[
			{ text: 'Name', value: 'name' },
			{ text: 'Version', value: 'version', width: '120px' },
			{ text: 'Status', value: 'status', width: '90px' },
			{ text: '', value: 'actions', width: '35px', sortable: false }
		]"
		v-if="page == 'plugins'"
	>
	<template v-slot:item.name="{ item }">
		<v-row>
			<v-col class="align-self-center my-1">
				<span class="body-1" v-show="item.name">{{ item.name }}<br /></span>
				<span class="caption"><v-btn small depressed><v-icon small class="mr-1">mdi-content-copy</v-icon> {{ item.slug }}</span></v-btn>
			</v-col>
		</v-row>
	</template>
	<template v-slot:item.status="{ item }">
		<div v-if="item.status == true || item.status == false">
			<v-switch hide-details v-model="item.status" @change="togglePlugin( item )" class="ma-0"></v-switch>
		</div>
		<div v-else>
			{{ item.status }}
		</div>
	</template>
	<template v-slot:item.actions="{ item }">
		<v-btn icon small @click="deletePlugin(item)" v-if="item.status == true || item.status == false">
			<v-icon>mdi-delete</v-icon>
		</v-btn>
	</template>
	</v-data-table>

	<v-snackbar v-model="snackbar.show">
	{{ snackbar.message }}
	</v-snackbar>

</v-main>
</v-app>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios@0.21.1/dist/axios.min.js"></script>
<script src="/wp-content/plugins/<?php echo basename( plugin_dir_path(__FILE__) ); ?>/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script>
	new Vue({
	el: '#app',
	vuetify: new Vuetify(),
	data: {
		content: { selected: "page", results: [], count: <?php echo count ( get_posts( [ "post_type" => "page", "posts_per_page" => "-1", "fields" => "ids" ] ) ); ?> },
		options: {},
		page: "content",
		current_user_email: "<?php echo $user->user_email; ?>",
		current_user_login: "<?php echo $user->user_login; ?>",
		current_user_display_name: "<?php echo $user->display_name; ?>",
		plugins: [],
		themes: [],
		snackbar: { show: false, message: "" },
		wp_nonce: "",
	},
	mounted() {
		if ( typeof wpApiSettings != "undefined" ) {
			this.wp_nonce = wpApiSettings.nonce
		}
		//this.fetchContent()
		this.fetchPlugins()
		this.fetchThemes()
	},
	watch: {
      options: {
        handler () {
          this.fetchContent()
        },
        deep: true,
      },
    },
	methods: {
		togglePlugin( plugin ) {
			if ( plugin.status == true ) {
				message = `Plugin "${plugin.name}" activated.`
				axios.post( '/wp-json/vuetifused/v1/manage/plugins', {
					command: 'activate',
					plugin: plugin.slug 
				},{
					headers: { 'X-WP-Nonce': this.wp_nonce },
				})
				.then( response => {
					this.fetchPlugins()
					this.snackbar.message = message
					this.snackbar.show = true 
				})
			} else {
				message = `Plugin "${plugin.name}" deactivated.`
				axios.post( '/wp-json/vuetifused/v1/manage/plugins', {
					command: 'deactivate',
					plugin: plugin.slug 
				},{
					headers: { 'X-WP-Nonce': this.wp_nonce },
				})
				.then( response => {
					this.fetchPlugins()
					this.snackbar.message = message
					this.snackbar.show = true 
				})
			}
		},
		deletePlugin( plugin ) {
			should_proceed = confirm( `Delete plugin "${plugin.name}"?` )
			if ( ! should_proceed ) {
				return
			}
			axios.post( '/wp-json/vuetifused/v1/manage/plugins', {
				command: 'delete',
				plugin: plugin.slug
			},{
				headers: { 'X-WP-Nonce': this.wp_nonce },
			})
			.then( response => {
				this.fetchPlugins()
				this.snackbar.message = `Deleted plugin ${plugin.name}.`
				this.snackbar.show = true 
			})
		},
		deleteTheme( theme ) {
			should_proceed = confirm( `Delete theme "${theme}"?` )
			if ( ! should_proceed ) {
				return
			}
			axios.post( '/wp-json/vuetifused/v1/manage/themes', {
				command: 'delete',
				theme: theme 
			},{
				headers: { 'X-WP-Nonce': this.wp_nonce },
			})
			.then( response => {
				this.fetchThemes()
				this.snackbar.message = `Deleted theme ${theme}.`
				this.snackbar.show = true 
			})
		},
		activateTheme( theme ) {
			axios.post( '/wp-json/vuetifused/v1/manage/themes', {
				command: 'activate',
				theme: theme 
			},{
				headers: { 'X-WP-Nonce': this.wp_nonce },
			})
			.then( response => {
				this.fetchThemes()
				this.snackbar.message = `Activated theme ${theme}.`
				this.snackbar.show = true 
			})
		},
		fetchThemes() {
			axios.get( '/wp-json/vuetifused/v1/site/themes', {
				headers: { 'X-WP-Nonce': this.wp_nonce }
			})
			.then( response => {
				this.themes = response.data
			})
		},
		fetchPlugins() {
			axios.get( '/wp-json/vuetifused/v1/site/plugins', {
				headers: { 'X-WP-Nonce': this.wp_nonce }
			})
			.then( response => {
				this.plugins = response.data
			})
		},
		fetchContent() {
			if ( this.options.page == "" ) {
				this.options.page = 1
			}
			axios.get( `/wp-json/vuetifused/v1/site/content/${ this.content.selected }/${ this.options.page }`, {
				headers: { 'X-WP-Nonce': this.wp_nonce }
			})
			.then( response => {
				this.content.results = response.data.results
				this.content.count = response.data.count
			})
		},
		signOut() {
			axios.post( '/wp-json/vuetifused/v1/login/', {
				command: "signOut" 
			})
			.then( response => {
				window.location = "/"
			})
		},
	},
	computed: {
		gravatar() {
			return 'https://www.gravatar.com/avatar/' + md5( this.current_user_email.trim().toLowerCase() ) + '?s=80&d=mp'
		},
		newContentLink() {
			if ( this.content.selected == "post" ) {
				return "/wp-admin/post-new.php"
			} else {
				return `/wp-admin/post-new.php?post_type=${ this.content.selected }`
			}
		}
	}
	})
</script>
</body>
</html>