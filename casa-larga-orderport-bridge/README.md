# Casa Larga OrderPort Bridge

A WordPress plugin that integrates with OrderPort UAPI v1 for wine product synchronization, custom label building, and e-commerce functionality.

## Features

- **Product Synchronization**: Syncs wine products and categories from OrderPort API
- **Custom Label Builder**: Interactive label design tool with Fabric.js
- **Age Verification**: Built-in age gate for wine sales compliance
- **REST API**: Full REST API endpoints for frontend integration
- **Admin Dashboard**: Complete admin interface for managing sync and settings
- **Security**: Comprehensive security measures including SQL injection prevention, XSS protection, and file upload validation

## Requirements

- WordPress 5.0+ (tested with 6.4+)
- PHP 7.4+ (working with 8.0+)
- OrderPort UAPI v1 access

## Installation

1. Upload the plugin files to `/wp-content/plugins/casa-larga-orderport-bridge/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your OrderPort API credentials in the admin settings
4. Run initial product synchronization

## Configuration

Configure your OrderPort API credentials in WordPress Admin > Casa Larga OrderPort > Settings.

## Security

This plugin implements comprehensive security measures:
- SQL injection prevention with prepared statements
- XSS protection with proper data escaping
- File upload validation and sanitization
- Nonce verification for form submissions
- Rate limiting for API calls

## Support

For support and documentation, please contact the development team.

## License

This plugin is proprietary software developed for Casa Larga Winery.
