// Page Editor Vue.js Application - Complete Template in JS
if (typeof Vue !== 'undefined') {
	const { createApp } = Vue;
	createApp({
		data() {
			return {
				pageData: {
					title: pageEditorData.page.title || '',
					slug: pageEditorData.page.slug || '',
					content: pageEditorData.page.content || '',
					status: pageEditorData.page.status || 'publish'
				},
				saving: false,
				message: '',
				messageType: 'success'
			};
		},
		computed: {
			pageUrl() {
				return pageEditorData.homeUrl + '/' + this.pageData.slug + '/';
			}
		},
		methods: {
			async savePage() {
				this.saving = true;
				this.message = '';
				try {
					const response = await fetch(pageEditorData.homeUrl + '/wp-json/wp/v2/pages/' + pageEditorData.pageId, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': pageEditorData.nonce
						},
						body: JSON.stringify({
							title: this.pageData.title,
							slug: this.pageData.slug,
							content: this.pageData.content,
							status: this.pageData.status
						})
					});
					const data = await response.json();
					if (response.ok) {
						this.message = 'Page saved successfully!';
						this.messageType = 'success';
						setTimeout(() => { this.message = ''; }, 3000);
					} else {
						this.message = data.message || 'Failed to save page';
						this.messageType = 'error';
					}
				} catch (error) {
					this.message = 'Error: ' + error.message;
					this.messageType = 'error';
				} finally {
					this.saving = false;
				}
			}
		}
	}).mount('#page-editor-app');
}
