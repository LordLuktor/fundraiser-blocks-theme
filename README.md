# Fundraiser Blocks Theme

A modern, secure WordPress block theme designed specifically for the Fundraiser Pro platform.

## Features

- 🎨 Modern, responsive design with beautiful animations
- 🔒 Security hardened with multiple security headers and best practices
- ⚡ Performance optimized with lazy loading and conditional script loading
- ♿ Accessibility ready (WCAG 2.1 AA)
- 📱 Mobile-first responsive design
- 🎯 Full Site Editing (FSE) support
- 🎨 Custom color palette and typography
- 🔧 WooCommerce compatible
- 🌐 Translation ready

## Security Features

This theme implements comprehensive security measures:

- **Security Headers**: X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- **Version Hiding**: WordPress version removed from all outputs
- **XML-RPC Disabled**: Prevents brute force attacks
- **User Enumeration Prevention**: Blocks author scans
- **SVG Sanitization**: Safe SVG upload handling
- **Nonce Verification**: All forms protected
- **Output Escaping**: All user input properly escaped

## Installation

1. Upload the theme to `/wp-content/themes/`
2. Activate from WordPress admin
3. Customize via Appearance → Editor

## Recommended Plugins

- **Fundraiser Pro**: Core fundraising functionality (required)
- **WooCommerce**: Payment processing (required)
- **Akismet**: Spam protection
- **Wordfence**: Additional security

## Customization

### Colors
Edit colors in `theme.json` under the `settings.color.palette` section.

### Typography
Modify font families and sizes in `theme.json` under `settings.typography`.

### Templates
All templates are in `/templates` and can be edited via the Site Editor or code.

### Patterns
Custom block patterns can be added to `/patterns`.

## Performance

The theme is optimized for performance:

- Minimal CSS/JS footprint
- Conditional asset loading
- Preconnect for external resources
- Deferred JavaScript loading
- SVG support for icons

## Support

For support, please contact Steinmetz Ltd or visit the Fundraiser Pro documentation.

## Changelog

### 1.0.0
- Initial release
- Full Site Editing support
- Security hardening
- WooCommerce integration
- Custom campaign templates

## License

GPL v2 or later

## Credits

Developed by Steinmetz Ltd
https://steinmetz.ltd
