// Product Request Form Vue.js Application - Complete Template in JS
if (typeof Vue !== 'undefined') {
	// Inject CSS styles into document head
	const styleId = 'product-request-styles';
	if (!document.getElementById(styleId)) {
		const style = document.createElement('style');
		style.id = styleId;
		style.textContent = `
			.product-request-container {
				background: white;
				padding: 2rem;
				border-radius: 12px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}
			.form-section {
				margin-bottom: 2rem;
			}
			.form-label {
				display: block;
				font-weight: 600;
				margin-bottom: 0.5rem;
				font-size: 1.1rem;
			}
			.form-select {
				width: 100%;
				padding: 0.75rem;
				border: 1px solid #d1d5db;
				border-radius: 6px;
				font-size: 1rem;
			}
			.form-error {
				color: #dc2626;
				font-size: 0.875rem;
				margin-top: 0.25rem;
			}
			.product-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
				gap: 1rem;
			}
			.product-card {
				display: flex;
				flex-direction: column;
				align-items: center;
				padding: 1rem;
				background: #f9fafb;
				border: 2px solid #d1d5db;
				border-radius: 8px;
				cursor: pointer;
				transition: all 0.2s;
			}
			.product-card:hover {
				transform: translateY(-2px);
			}
			.product-card.selected {
				border-color: #667eea;
				background: #ede9fe;
			}
			.product-card input[type="checkbox"] {
				display: none;
			}
			.product-icon {
				font-size: 2rem;
				margin-bottom: 0.5rem;
			}
			.product-label {
				font-weight: 600;
				text-align: center;
			}
			.file-upload-zone {
				border: 2px dashed #d1d5db;
				border-radius: 8px;
				padding: 2rem;
				text-align: center;
				background: #f9fafb;
				cursor: pointer;
			}
			.file-list {
				margin-top: 1rem;
			}
			.file-item {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 0.75rem;
				background: white;
				border: 1px solid #d1d5db;
				border-radius: 6px;
				margin-bottom: 0.5rem;
			}
			.file-info {
				display: flex;
				align-items: center;
				gap: 0.5rem;
			}
			.file-remove-btn {
				padding: 0.25rem 0.5rem;
				background: #fee2e2;
				color: #991b1b;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				font-size: 0.875rem;
			}
			.form-textarea {
				width: 100%;
				padding: 0.75rem;
				border: 1px solid #d1d5db;
				border-radius: 6px;
				font-size: 1rem;
				resize: vertical;
			}
			.info-box {
				padding: 1.5rem;
				background: #f0f9ff;
				border: 1px solid #bae6fd;
				border-radius: 8px;
				margin-bottom: 2rem;
			}
			.info-box-title {
				font-weight: 600;
				margin-bottom: 0.5rem;
			}
			.info-list {
				margin: 0;
				padding-left: 1.5rem;
				color: #0c4a6e;
			}
			.info-list li {
				margin-bottom: 0.5rem;
			}
			.submit-btn {
				padding: 1rem 2rem;
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				border: none;
				border-radius: 8px;
				font-weight: 600;
				font-size: 1rem;
				cursor: pointer;
				width: 100%;
				transition: all 0.2s;
			}
			.submit-btn:hover:not(:disabled) {
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
			}
			.submit-btn:disabled {
				opacity: 0.6;
				cursor: not-allowed;
			}
			.message-box {
				padding: 1rem;
				border-radius: 6px;
				margin-top: 1rem;
			}
			.message-success {
				background: #d1fae5;
				color: #065f46;
			}
			.message-error {
				background: #fee2e2;
				color: #991b1b;
			}
		`;
		document.head.appendChild(style);
	}

	const { createApp } = Vue;
	createApp({
		template: `
			<div class="product-request-container">
				<!-- Campaign Selection -->
				<div class="form-section">
					<label class="form-label">Select Campaign *</label>
					<select v-model="request.campaign_id" class="form-select">
						<option value="">-- Select a campaign --</option>
						<option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
							{{ campaign.title }}
						</option>
					</select>
					<div v-if="errors.campaign" class="form-error">{{ errors.campaign }}</div>
				</div>

				<!-- Product Selection -->
				<div class="form-section">
					<label class="form-label">Select Products *</label>
					<div class="product-grid">
						<label v-for="product in products" :key="product.id"
							   class="product-card"
							   :class="{selected: request.products.includes(product.id)}">
							<input type="checkbox" v-model="request.products" :value="product.id">
							<div class="product-icon">{{ product.icon }}</div>
							<div class="product-label">{{ product.label }}</div>
						</label>
					</div>
					<div v-if="errors.products" class="form-error">{{ errors.products }}</div>
				</div>

				<!-- File Upload -->
				<div class="form-section">
					<label class="form-label">Upload Artwork Files *</label>
					<div class="file-upload-zone" @click="$refs.fileInput.click()">
						<input type="file" @change="handleFileUpload" multiple accept=".jpg,.jpeg,.png,.svg,.pdf"
							   style="display: none;" ref="fileInput">
						<div style="font-size: 3rem; margin-bottom: 0.5rem;">📁</div>
						<div style="font-weight: 600; margin-bottom: 0.5rem;">Click to upload artwork files</div>
						<div style="color: #666; font-size: 0.875rem;">Supported formats: JPG, PNG, SVG, PDF (max 10MB per file)</div>
					</div>

					<div v-if="request.files.length > 0" class="file-list">
						<div style="font-weight: 600; margin-bottom: 0.5rem;">Selected Files ({{ request.files.length }}):</div>
						<div class="file-item" v-for="(file, index) in request.files" :key="index">
							<div class="file-info">
								<span style="font-size: 1.5rem;">📄</span>
								<span>{{ file.name }}</span>
								<span style="color: #666; font-size: 0.875rem;">({{ formatFileSize(file.size) }})</span>
							</div>
							<button type="button" @click="removeFile(index)" class="file-remove-btn">Remove</button>
						</div>
					</div>
					<div v-if="errors.files" class="form-error">{{ errors.files }}</div>
				</div>

				<!-- Notes -->
				<div class="form-section">
					<label class="form-label">Additional Notes</label>
					<textarea v-model="request.notes" rows="4" class="form-textarea"
							  placeholder="Any special instructions or details about your product request..."></textarea>
				</div>

				<!-- Info Box -->
				<div class="info-box">
					<div class="info-box-title">ℹ️ What happens next?</div>
					<ol class="info-list">
						<li>We'll review your artwork and product selections</li>
						<li>Products will be created in our print provider systems</li>
						<li>Once ready, products will appear in your campaign</li>
						<li>You'll be able to edit prices from your campaign dashboard</li>
					</ol>
				</div>

				<!-- Submit Button -->
				<div>
					<button type="button" @click="submitRequest" :disabled="submitting" class="submit-btn">
						<span v-if="!submitting">🚀 Submit Product Request</span>
						<span v-else>⏳ Submitting...</span>
					</button>
					<div v-if="message" class="message-box" :class="messageType === 'success' ? 'message-success' : 'message-error'">
						{{ message }}
					</div>
				</div>
			</div>
		`,
		data() {
			return {
				campaigns: fundraiserData.campaigns || [],
				products: [
					{id: 'tshirts', label: 'T-Shirts', icon: '👕'},
					{id: 'hoodies', label: 'Hoodies', icon: '🧥'},
					{id: 'mugs', label: 'Mugs', icon: '☕'},
					{id: 'tumblers_20oz', label: '20oz Tumblers', icon: '🥤'},
					{id: 'tumblers_40oz', label: '40oz Tumblers', icon: '🍺'},
					{id: 'hats', label: 'Hats/Caps', icon: '🧢'},
					{id: 'bags', label: 'Tote Bags', icon: '👜'},
					{id: 'blankets', label: 'Blankets', icon: '🛏️'},
					{id: 'posters', label: 'Posters', icon: '🖼️'},
					{id: 'stickers', label: 'Stickers', icon: '✨'}
				],
				request: {
					campaign_id: "",
					products: [],
					files: [],
					notes: ""
				},
				errors: {},
				submitting: false,
				message: "",
				messageType: "success"
			};
		},
		methods: {
			handleFileUpload(event) {
				const files = Array.from(event.target.files);
				for (const file of files) {
					if (file.size > 10485760) {
						alert("File " + file.name + " is too large. Maximum size is 10MB.");
						continue;
					}
					this.request.files.push(file);
				}
				event.target.value = "";
			},
			removeFile(index) {
				this.request.files.splice(index, 1);
			},
			formatFileSize(bytes) {
				if (bytes === 0) return "0 Bytes";
				const k = 1024;
				const sizes = ["Bytes", "KB", "MB"];
				const i = Math.floor(Math.log(bytes) / Math.log(k));
				return Math.round(bytes / Math.pow(k, i) * 100) / 100 + " " + sizes[i];
			},
			validate() {
				this.errors = {};
				if (!this.request.campaign_id) this.errors.campaign = "Please select a campaign";
				if (this.request.products.length === 0) this.errors.products = "Please select at least one product";
				if (this.request.files.length === 0) this.errors.files = "Please upload at least one artwork file";
				return Object.keys(this.errors).length === 0;
			},
			async submitRequest() {
				if (!this.validate()) return;
				this.submitting = true;
				this.message = "";
				try {
					const formData = new FormData();
					formData.append("campaign_id", this.request.campaign_id);
					formData.append("products", JSON.stringify(this.request.products));
					formData.append("notes", this.request.notes);
					for (let i = 0; i < this.request.files.length; i++) {
						formData.append("artwork[]", this.request.files[i]);
					}
					const response = await fetch(fundraiserData.apiUrl + "product-requests", {
						method: "POST",
						headers: {
							"X-WP-Nonce": fundraiserData.nonce
						},
						body: formData
					});
					const data = await response.json();
					if (response.ok) {
						this.message = "Product request submitted successfully! We'll review it and add products to your campaign.";
						this.messageType = "success";
						this.request = {campaign_id: "", products: [], files: [], notes: ""};
						setTimeout(() => {
							window.location.href = fundraiserData.homeUrl + "/my-campaigns/";
						}, 2000);
					} else {
						this.message = data.message || "Failed to submit product request";
						this.messageType = "error";
					}
				} catch (error) {
					this.message = "Error: " + error.message;
					this.messageType = "error";
				} finally {
					this.submitting = false;
				}
			}
		}
	}).mount("#product-request-app");
}
