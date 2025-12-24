// Campaign Wizard Vue.js Application - Complete Template in JS
if (typeof Vue !== 'undefined') {
	// Inject CSS styles into document head
	const styleId = 'campaign-wizard-styles';
	if (!document.getElementById(styleId)) {
		const style = document.createElement('style');
		style.id = styleId;
		style.textContent = `
			.campaign-wizard-container {
				max-width: 800px;
				margin: 2rem auto;
				padding: 2rem;
				background: white;
				border-radius: 12px;
				box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			}
			.wizard-header {
				text-align: center;
				margin-bottom: 3rem;
			}
			.wizard-header h2 {
				font-size: 2rem;
				font-weight: 700;
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
				margin-bottom: 0.5rem;
			}
			.wizard-progress {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 3rem;
				position: relative;
			}
			.wizard-step {
				display: flex;
				flex-direction: column;
				align-items: center;
				position: relative;
				z-index: 2;
				flex: 1;
			}
			.wizard-step-circle {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 50px;
				height: 50px;
				border-radius: 50%;
				margin-bottom: 0.5rem;
				font-weight: 700;
				font-size: 1.125rem;
				transition: all 0.3s;
				background: #e5e7eb;
				color: #6b7280;
			}
			.wizard-step-circle.active {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				transform: scale(1.1);
			}
			.wizard-step-circle.completed {
				background: linear-gradient(135deg, #10b981 0%, #059669 100%);
				color: white;
			}
			.wizard-step-label {
				font-size: 0.875rem;
				font-weight: 600;
				color: #6b7280;
				text-align: center;
			}
			.wizard-step-label.active {
				color: #667eea;
			}
			.wizard-progress-line {
				position: absolute;
				top: 25px;
				left: 0;
				right: 0;
				height: 3px;
				background: #e5e7eb;
				z-index: 1;
			}
			.wizard-progress-fill {
				height: 100%;
				background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
				transition: width 0.3s;
			}
			.wizard-content {
				min-height: 400px;
				margin-bottom: 2rem;
			}
			.form-group {
				margin-bottom: 1.5rem;
			}
			.form-label {
				display: block;
				font-weight: 600;
				margin-bottom: 0.5rem;
				color: #374151;
			}
			.form-input {
				width: 100%;
				padding: 0.75rem;
				border: 2px solid #d1d5db;
				border-radius: 8px;
				font-size: 1rem;
				transition: border-color 0.2s;
			}
			.form-input:focus {
				outline: none;
				border-color: #667eea;
			}
			.form-textarea {
				width: 100%;
				min-height: 120px;
				padding: 0.75rem;
				border: 2px solid #d1d5db;
				border-radius: 8px;
				font-size: 1rem;
				resize: vertical;
				transition: border-color 0.2s;
			}
			.form-textarea:focus {
				outline: none;
				border-color: #667eea;
			}
			.form-error {
				color: #dc2626;
				font-size: 0.875rem;
				margin-top: 0.25rem;
			}
			.method-grid {
				display: grid;
				gap: 1rem;
			}
			.method-card {
				display: flex;
				align-items: center;
				padding: 1.5rem;
				background: #f9fafb;
				border: 2px solid #d1d5db;
				border-radius: 8px;
				cursor: pointer;
				transition: all 0.2s;
			}
			.method-card:hover {
				transform: translateY(-2px);
				box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			}
			.method-card.selected {
				border-color: #667eea;
				background: #ede9fe;
			}
			.method-card input[type="checkbox"] {
				width: 20px;
				height: 20px;
				margin-right: 1rem;
				cursor: pointer;
			}
			.method-info h4 {
				font-size: 1.125rem;
				font-weight: 600;
				margin-bottom: 0.25rem;
				color: #1f2937;
			}
			.method-info p {
				color: #6b7280;
				font-size: 0.875rem;
				margin: 0;
			}
			.review-section {
				background: #f9fafb;
				padding: 1.5rem;
				border-radius: 8px;
				margin-bottom: 1rem;
			}
			.review-section h3 {
				font-size: 1.25rem;
				font-weight: 600;
				margin-bottom: 1rem;
				color: #1f2937;
			}
			.review-item {
				display: flex;
				justify-content: space-between;
				padding: 0.75rem 0;
				border-bottom: 1px solid #e5e7eb;
			}
			.review-item:last-child {
				border-bottom: none;
			}
			.review-label {
				font-weight: 600;
				color: #6b7280;
			}
			.review-value {
				color: #1f2937;
			}
			.wizard-actions {
				display: flex;
				justify-content: space-between;
				gap: 1rem;
				padding-top: 2rem;
				border-top: 2px solid #e5e7eb;
			}
			.btn {
				padding: 0.75rem 2rem;
				border: none;
				border-radius: 8px;
				font-size: 1rem;
				font-weight: 600;
				cursor: pointer;
				transition: all 0.2s;
				text-decoration: none;
			}
			.btn:disabled {
				opacity: 0.5;
				cursor: not-allowed;
			}
			.btn-secondary {
				background: #e5e7eb;
				color: #374151;
			}
			.btn-secondary:hover:not(:disabled) {
				background: #d1d5db;
			}
			.btn-primary {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
			}
			.btn-primary:hover:not(:disabled) {
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
			}
			.error-message {
				background: #fee2e2;
				border: 2px solid #dc2626;
				color: #dc2626;
				padding: 1rem;
				border-radius: 8px;
				margin-bottom: 1rem;
				font-weight: 600;
			}
		`;
		document.head.appendChild(style);
	}

	const { createApp } = Vue;
	createApp({
		template: `
			<div class="campaign-wizard-container">

				<div class="wizard-header">
					<h2>Create New Campaign</h2>
					<p style="color: #6b7280;">Follow the steps below to set up your fundraising campaign</p>
				</div>

				<div class="wizard-progress">
					<div class="wizard-progress-line">
						<div class="wizard-progress-fill" :style="{width: ((currentStep - 1) / 3 * 100) + '%'}"></div>
					</div>
					<div class="wizard-step">
						<div class="wizard-step-circle" :class="{active: currentStep === 1, completed: currentStep > 1}">1</div>
						<div class="wizard-step-label" :class="{active: currentStep === 1}">Basic Info</div>
					</div>
					<div class="wizard-step">
						<div class="wizard-step-circle" :class="{active: currentStep === 2, completed: currentStep > 2}">2</div>
						<div class="wizard-step-label" :class="{active: currentStep === 2}">Methods</div>
					</div>
					<div class="wizard-step">
						<div class="wizard-step-circle" :class="{active: currentStep === 3, completed: currentStep > 3}">3</div>
						<div class="wizard-step-label" :class="{active: currentStep === 3}">Media</div>
					</div>
					<div class="wizard-step">
						<div class="wizard-step-circle" :class="{active: currentStep === 4}">4</div>
						<div class="wizard-step-label" :class="{active: currentStep === 4}">Review</div>
					</div>
				</div>

				<div class="wizard-content">
					<!-- Step 1: Basic Info -->
					<div v-if="currentStep === 1">
						<div class="form-group">
							<label class="form-label">Campaign Title *</label>
							<input type="text" v-model="campaign.title" class="form-input" placeholder="e.g., 2024 School Fundraiser">
							<div v-if="errors.title" class="form-error">{{ errors.title }}</div>
						</div>

						<div class="form-group">
							<label class="form-label">Campaign Description *</label>
							<textarea v-model="campaign.description" class="form-textarea" placeholder="Describe your campaign goals and what the funds will be used for..."></textarea>
							<div v-if="errors.description" class="form-error">{{ errors.description }}</div>
						</div>

						<div class="form-group">
							<label class="form-label">Fundraising Goal ($) *</label>
							<input type="number" v-model.number="campaign.goal" class="form-input" min="0" step="100">
							<div v-if="errors.goal" class="form-error">{{ errors.goal }}</div>
						</div>

						<div class="form-group">
							<label class="form-label">Campaign Duration (days) *</label>
							<input type="number" v-model.number="campaign.duration" class="form-input" min="1" max="365">
							<div v-if="errors.duration" class="form-error">{{ errors.duration }}</div>
						</div>
					</div>

					<!-- Step 2: Fundraising Methods -->
					<div v-if="currentStep === 2">
						<p style="color: #6b7280; margin-bottom: 1.5rem;">Select the fundraising methods you want to enable for this campaign:</p>

						<div v-if="errors.methods" class="error-message">{{ errors.methods }}</div>

						<div class="method-grid">
							<label class="method-card" :class="{selected: campaign.donations_enabled}">
								<input type="checkbox" v-model="campaign.donations_enabled">
								<div class="method-info">
									<h4>💰 Direct Donations</h4>
									<p>Accept one-time and recurring donations from supporters</p>
								</div>
							</label>

							<label class="method-card" :class="{selected: campaign.products_enabled}">
								<input type="checkbox" v-model="campaign.products_enabled">
								<div class="method-info">
									<h4>🛍️ Product Sales</h4>
									<p>Sell custom merchandise and fundraising products</p>
								</div>
							</label>

							<label class="method-card" :class="{selected: campaign.raffles_enabled}">
								<input type="checkbox" v-model="campaign.raffles_enabled">
								<div class="method-info">
									<h4>🎫 Raffle Tickets</h4>
									<p>Run raffles and prize drawings to raise funds</p>
								</div>
							</label>
						</div>
					</div>

					<!-- Step 3: Media -->
					<div v-if="currentStep === 3">
						<div class="form-group">
							<label class="form-label">Campaign Video URL (Optional)</label>
							<input type="url" v-model="campaign.video_url" class="form-input" placeholder="https://youtube.com/watch?v=...">
							<p style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">Add a YouTube or Vimeo video to your campaign page</p>
						</div>

						<div style="background: #eff6ff; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
							<h4 style="color: #1e40af; margin-bottom: 0.5rem; font-weight: 600;">💡 Pro Tip</h4>
							<p style="color: #1e40af; margin: 0; font-size: 0.875rem;">Videos can increase donations by up to 80%! Share your story and connect with supporters emotionally.</p>
						</div>
					</div>

					<!-- Step 4: Review & Create -->
					<div v-if="currentStep === 4">
						<p style="color: #6b7280; margin-bottom: 1.5rem;">Review your campaign details before creating:</p>

						<div v-if="createError" class="error-message">{{ createError }}</div>

						<div class="review-section">
							<h3>Basic Information</h3>
							<div class="review-item">
								<span class="review-label">Title:</span>
								<span class="review-value">{{ campaign.title }}</span>
							</div>
							<div class="review-item">
								<span class="review-label">Description:</span>
								<span class="review-value">{{ campaign.description.substring(0, 100) }}{{ campaign.description.length > 100 ? '...' : '' }}</span>
							</div>
							<div class="review-item">
								<span class="review-label">Goal:</span>
								<span class="review-value">\${{ campaign.goal.toLocaleString() }}</span>
							</div>
							<div class="review-item">
								<span class="review-label">Duration:</span>
								<span class="review-value">{{ campaign.duration }} days</span>
							</div>
						</div>

						<div class="review-section">
							<h3>Fundraising Methods</h3>
							<div class="review-item">
								<span class="review-label">Direct Donations:</span>
								<span class="review-value">{{ campaign.donations_enabled ? '✅ Enabled' : '❌ Disabled' }}</span>
							</div>
							<div class="review-item">
								<span class="review-label">Product Sales:</span>
								<span class="review-value">{{ campaign.products_enabled ? '✅ Enabled' : '❌ Disabled' }}</span>
							</div>
							<div class="review-item">
								<span class="review-label">Raffle Tickets:</span>
								<span class="review-value">{{ campaign.raffles_enabled ? '✅ Enabled' : '❌ Disabled' }}</span>
							</div>
						</div>

						<div class="review-section" v-if="campaign.video_url">
							<h3>Media</h3>
							<div class="review-item">
								<span class="review-label">Video URL:</span>
								<span class="review-value">{{ campaign.video_url }}</span>
							</div>
						</div>
					</div>
				</div>

				<div class="wizard-actions">
					<button v-if="currentStep > 1" @click="prevStep" class="btn btn-secondary" :disabled="creating">
						← Previous
					</button>
					<div v-else></div>

					<button v-if="currentStep < 4" @click="nextStep" class="btn btn-primary">
						Next →
					</button>
					<button v-else @click="createCampaign" class="btn btn-primary" :disabled="creating">
						{{ creating ? 'Creating...' : '✨ Create Campaign' }}
					</button>
				</div>
			</div>
		`,
		data() {
			return {
				currentStep: 1,
				campaign: {
					title: '',
					description: '',
					goal: 10000,
					duration: 30,
					donations_enabled: true,
					products_enabled: false,
					raffles_enabled: false,
					video_url: ''
				},
				errors: {},
				creating: false,
				createError: ''
			};
		},
		methods: {
			validateStep() {
				this.errors = {};
				if (this.currentStep === 1) {
					if (!this.campaign.title.trim()) this.errors.title = 'Campaign title is required';
					if (!this.campaign.description.trim()) this.errors.description = 'Campaign description is required';
					if (!this.campaign.goal || this.campaign.goal <= 0) this.errors.goal = 'Please enter a valid goal amount';
					if (!this.campaign.duration || this.campaign.duration < 1) this.errors.duration = 'Please enter a valid duration';
				}
				if (this.currentStep === 2) {
					if (!this.campaign.donations_enabled && !this.campaign.products_enabled && !this.campaign.raffles_enabled) {
						this.errors.methods = 'Please select at least one fundraising method';
					}
				}
				return Object.keys(this.errors).length === 0;
			},
			nextStep() {
				if (this.validateStep()) {
					this.currentStep++;
					window.scrollTo({top: 0, behavior: 'smooth'});
				}
			},
			prevStep() {
				this.currentStep--;
				window.scrollTo({top: 0, behavior: 'smooth'});
			},
			async createCampaign() {
				if (!this.validateStep()) return;
				this.creating = true;
				this.createError = '';
				try {
					const response = await fetch(fundraiserData.apiUrl + 'campaigns', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': fundraiserData.nonce
						},
						body: JSON.stringify(this.campaign)
					});
					const data = await response.json();
					if (response.ok) {
						window.location.href = fundraiserData.homeUrl + '/campaign-detail/?campaign_id=' + data.id + '&tab=overview';
					} else {
						this.createError = data.message || 'Failed to create campaign';
					}
				} catch (error) {
					this.createError = 'Error: ' + error.message;
				} finally {
					this.creating = false;
				}
			}
		}
	}).mount('#campaign-wizard-app');
}
