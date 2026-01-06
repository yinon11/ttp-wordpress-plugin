<?php
/**
 * Admin Styles
 * 
 * All CSS for the admin interface
 * 
 * FIX: Expanded card widths to use full available space
 */

if (!defined('ABSPATH')) exit;

/**
 * Get admin styles CSS content
 * 
 * WordPress Plugin Review: Returns CSS as string for wp_add_inline_style()
 * 
 * @return string CSS content
 */
function talktopc_get_admin_styles_css() {
    return '
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* FIX: Wider layout - use available space */
        .talktopc-admin-wrap {
            max-width: 1200px;
            padding: 20px;
            width: 100%;
        }
        
        .wp-header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .wp-header h1 {
            font-size: 23px;
            font-weight: 400;
        }
        
        .wp-header .version {
            font-size: 11px;
            color: #666;
            background: #e5e5e5;
            padding: 2px 8px;
            border-radius: 3px;
        }
        
        /* FIX: Cards take full width */
        .card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px 24px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Force all direct children of wrap to be full width */
        .talktopc-admin-wrap > * {
            width: 100%;
            max-width: 100%;
        }
        
        .card h2 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card h2 .icon {
            font-size: 16px;
        }
        
        .card h3 {
            font-size: 13px;
            font-weight: 600;
            margin: 20px 0 10px 0;
            color: #50575e;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        
        .form-row label {
            width: 140px;
            font-size: 13px;
            padding-top: 8px;
            flex-shrink: 0;
            color: #1d2327;
        }
        
        .form-row .field {
            flex: 1;
            max-width: 600px; /* FIX: Wider fields */
        }
        
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            padding: 8px 12px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            max-width: 500px; /* FIX: Wider inputs */
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
            max-width: 100%; /* FIX: Textarea takes full field width */
        }
        
        .small-input {
            max-width: 80px !important;
        }
        
        .description {
            font-size: 12px;
            color: #646970;
            margin-top: 4px;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #f6f7f7;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            color: #1d2327;
            transition: all 0.15s;
        }
        
        .button:hover {
            background: #f0f0f1;
            border-color: #8c8f94;
            color: #1d2327;
        }
        
        .button-primary {
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
        }
        
        .button-primary:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
        }
        
        .button-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .button-link-delete {
            color: #b32d2e;
            border-color: transparent;
            background: transparent;
            padding: 4px 8px;
        }
        
        .button-link-delete:hover {
            color: #a00;
            background: #f8e8e8;
        }
        
        .button-hero {
            padding: 12px 24px;
            font-size: 16px;
        }
        
        /* =============================================
           Credits Box - Multiple States
           ============================================= */
        .credits-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .credits-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .credits-icon {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }
        
        .credits-info h3 {
            font-size: 11px;
            font-weight: 500;
            opacity: 0.85;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }
        
        .credits-amount {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        
        .credits-info .amount {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .credits-info .unit {
            font-size: 16px;
            font-weight: 400;
            opacity: 0.85;
        }
        
        .credits-label {
            font-size: 13px;
            opacity: 0.75;
            margin-top: 2px;
        }
        
        .credits-warning {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            margin-top: 8px;
        }
        
        .credits-warning.visible {
            display: inline-flex;
        }
        
        .credits-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        
        .credits-hint {
            font-size: 11px;
            opacity: 0.7;
        }
        
        .credits-box .button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            color: white;
            transition: all 0.15s;
        }
        
        .credits-box .button:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-1px);
        }
        
        /* Loading spinner */
        .credits-info .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: talktopc-spin 0.8s linear infinite;
        }
        
        /* State: Loading */
        .credits-box.loading {
            background: linear-gradient(135deg, #a8a8a8 0%, #888 100%);
            box-shadow: 0 4px 15px rgba(136, 136, 136, 0.3);
        }
        
        /* State: Low Credits (10-100 minutes) */
        .credits-box.low-credits {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }
        
        /* State: Critical (<10 minutes) */
        .credits-box.critical-credits {
            background: linear-gradient(135deg, #ff6b6b 0%, #c0392b 100%);
            box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);
            animation: pulse-critical 2s infinite;
        }
        
        @keyframes pulse-critical {
            0%, 100% { box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3); }
            50% { box-shadow: 0 4px 25px rgba(192, 57, 43, 0.5); }
        }
        
        .credits-box.critical-credits .button {
            background: white;
            color: #c0392b;
            border-color: white;
            font-weight: 600;
        }
        
        .credits-box.critical-credits .button:hover {
            background: #fff5f5;
            color: #c0392b;
        }
        
        /* State: No Credits */
        .credits-box.no-credits {
            background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
            box-shadow: 0 4px 15px rgba(45, 52, 54, 0.3);
        }
        
        .credits-box.no-credits .credits-icon {
            background: rgba(255,255,255,0.1);
        }
        
        .credits-box.no-credits .button {
            background: #00b894;
            border-color: #00b894;
        }
        
        .credits-box.no-credits .button:hover {
            background: #00a185;
            border-color: #00a185;
            color: white;
        }
        
        /* State: Error */
        .credits-box.error {
            background: linear-gradient(135deg, #636e72 0%, #2d3436 100%);
            box-shadow: 0 4px 15px rgba(45, 52, 54, 0.3);
        }
        
        .credits-box.error .credits-icon {
            background: rgba(255,255,255,0.1);
        }
        
        .credits-box .retry-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            margin-left: 8px;
            vertical-align: middle;
        }
        
        .credits-box .retry-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .credits-box {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .credits-left {
                flex-direction: column;
            }
            
            .credits-right {
                align-items: center;
            }
        }
        
        .status-connected {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            background: #00a32a;
            border-radius: 50%;
        }
        
        .status-email {
            color: #50575e;
        }
        
        /* FIX: Agent selector full width */
        .agent-selector-big {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border: 2px solid #e2e4e7;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            width: 100%;
        }
        
        .agent-selector-big label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1d2327;
        }
        
        .agent-selector-big select {
            width: 100%;
            max-width: 100%;
            padding: 14px 16px;
            font-size: 16px;
            border: 2px solid #c3c4c7;
            border-radius: 6px;
            background: #fff;
        }
        
        .agent-selector-big select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }
        
        .creating-agent-box {
            background: linear-gradient(135deg, #f0f6fc 0%, #e8f4f8 100%);
            border: 1px solid #c3e6fb;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .creating-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #c3e6fb;
            border-top-color: #2271b1;
            border-radius: 50%;
            animation: talktopc-spin 1s linear infinite;
        }
        
        @keyframes talktopc-spin {
            to { transform: rotate(360deg); }
        }
        
        /* Setup Overlay Modal */
        .talktopc-setup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100000;
        }
        
        .talktopc-setup-modal {
            background: #fff;
            padding: 40px 50px;
            border-radius: 8px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .talktopc-setup-modal h2 {
            margin: 20px 0 10px;
            color: #1d2327;
            border: none;
            padding: 0;
            font-size: 20px;
        }
        
        .talktopc-setup-modal p {
            color: #666;
            margin: 0 0 10px;
            line-height: 1.5;
        }
        
        .talktopc-setup-modal .talktopc-setup-note {
            font-size: 12px;
            color: #999;
            margin-top: 15px;
        }
        
        .talktopc-setup-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #7C3AED;
            border-radius: 50%;
            animation: talktopc-spin 1s linear infinite;
            margin: 0 auto;
        }
        
        /* Background Setup Banner */
        .talktopc-background-setup-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .talktopc-banner-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: talktopc-spin 1s linear infinite;
            flex-shrink: 0;
        }
        
        .talktopc-banner-text {
            flex: 1;
        }
        
        .talktopc-banner-text strong {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .talktopc-banner-text span {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .creating-text {
            flex: 1;
        }
        
        .creating-text strong {
            display: block;
            font-size: 15px;
            margin-bottom: 5px;
            color: #1d2327;
        }
        
        .creating-text p {
            margin: 0;
            font-size: 13px;
            color: #646970;
        }
        
        /* FIX: Agent settings full width */
        .agent-settings {
            border: 1px solid #e2e4e7;
            border-radius: 6px;
            margin-top: 20px;
            overflow: hidden;
            width: 100%;
        }
        
        .agent-settings-header {
            padding: 14px 18px;
            background: #f8f9fa;
            border-bottom: 1px solid #e2e4e7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background 0.15s;
        }
        
        .agent-settings-header:hover {
            background: #f0f0f1;
        }
        
        .agent-settings-header h3 {
            margin: 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .agent-settings-body {
            padding: 24px;
        }
        
        .agent-settings.collapsed .agent-settings-body {
            display: none;
        }
        
        .agent-settings.collapsed .agent-settings-header {
            border-bottom: none;
        }
        
        .agent-settings .arrow {
            transition: transform 0.2s;
            color: #666;
        }
        
        .agent-settings.collapsed .arrow {
            transform: rotate(-90deg);
        }
        
        /* FIX: Quick links responsive grid */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: #f8f9fa;
            border: 1px solid #e2e4e7;
            border-radius: 6px;
            text-decoration: none;
            color: #1d2327;
            transition: all 0.15s;
        }
        
        .quick-link:hover {
            background: #f0f6fc;
            border-color: #2271b1;
        }
        
        .quick-link .icon {
            font-size: 22px;
        }
        
        .quick-link .text {
            font-size: 13px;
            font-weight: 500;
        }
        
        .save-area {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .save-status {
            font-size: 13px;
            color: #646970;
        }
        
        .save-status.saved {
            color: #00a32a;
        }
        
        .inline-fields {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .inline-field {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .inline-field label {
            width: auto;
            padding: 0;
            font-size: 13px;
        }
        
        .create-agent-section {
            background: #f8f9fa;
            border: 2px dashed #c3c4c7;
            border-radius: 8px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .create-agent-section h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #1d2327;
        }
        
        .create-agent-section p {
            margin: 0 0 15px 0;
            font-size: 13px;
            color: #646970;
        }
        
        .create-agent-inline {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .create-agent-inline input {
            flex: 1;
            max-width: 400px;
        }
        
        .help-box {
            background: #f0f6fc;
            border: 1px solid #c3e6fb;
            border-radius: 4px;
            padding: 15px 18px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .help-box strong {
            display: block;
            margin-bottom: 5px;
            color: #1d2327;
        }
        
        .default-agent-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: #f8f9fa;
            border: 1px solid #e2e4e7;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .default-agent-bar .icon {
            font-size: 24px;
        }
        
        .default-agent-bar .text {
            flex: 1;
        }
        
        .default-agent-bar .text strong {
            display: block;
            font-size: 14px;
        }
        
        .default-agent-bar .text span {
            font-size: 12px;
            color: #646970;
        }
        
        .rule-card {
            border: 1px solid #c3c4c7;
            border-radius: 6px;
            margin-bottom: 10px;
            background: #fff;
            overflow: hidden;
        }
        
        .rule-card.disabled-rule {
            border-color: #d63638;
            background: #fcf0f1;
        }
        
        .rule-header {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            gap: 12px;
            cursor: pointer;
            transition: background 0.15s;
        }
        
        .rule-header:hover {
            background: #f8f9fa;
        }
        
        .rule-card.disabled-rule .rule-header:hover {
            background: #fce8e8;
        }
        
        .rule-drag-handle {
            color: #c3c4c7;
            cursor: grab;
            font-size: 16px;
        }
        
        .rule-drag-handle:hover {
            color: #666;
        }
        
        .rule-icon {
            font-size: 18px;
        }
        
        .rule-target {
            flex: 1;
        }
        
        .rule-target .name {
            font-weight: 500;
            font-size: 14px;
            color: #1d2327;
        }
        
        .rule-target .type {
            font-size: 11px;
            color: #646970;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .rule-agent-select {
            min-width: 180px;
        }
        
        .rule-agent-select select {
            width: 100%;
            padding: 6px 10px;
            font-size: 13px;
        }
        
        .rule-actions {
            display: flex;
            gap: 5px;
        }
        
        .rule-delete {
            color: #b32d2e;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            border-radius: 4px;
            transition: background 0.15s;
        }
        
        .rule-delete:hover {
            background: #fce8e8;
        }
        
        .rule-expand {
            color: #666;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
            transition: transform 0.2s;
        }
        
        .rule-card.open .rule-expand {
            transform: rotate(180deg);
        }
        
        .rule-settings {
            padding: 20px;
            display: none;
            border-top: 1px solid #e5e5e5;
            background: #f8f9fa;
        }
        
        .rule-card.open .rule-settings {
            display: block;
        }
        
        .rule-settings .form-row label {
            width: 120px;
        }
        
        .add-rule-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px;
            border: 2px dashed #c3c4c7;
            border-radius: 6px;
            background: #f8f9fa;
            cursor: pointer;
            color: #646970;
            font-size: 14px;
            transition: all 0.15s;
        }
        
        .add-rule-btn:hover {
            border-color: #2271b1;
            background: #f0f6fc;
            color: #2271b1;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #646970;
        }
        
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #1d2327;
            font-size: 16px;
        }
        
        .empty-state p {
            margin-bottom: 20px;
            font-size: 14px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .modal-overlay.hidden {
            display: none;
        }
        
        .modal {
            background: white;
            border-radius: 8px;
            width: 550px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            font-size: 16px;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            line-height: 1;
        }
        
        .modal-close:hover {
            color: #1d2327;
        }
        
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8f9fa;
        }
        
        .page-selector h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #646970;
            margin: 18px 0 8px 0;
            letter-spacing: 0.5px;
        }
        
        .page-selector h4:first-child {
            margin-top: 0;
        }
        
        .page-selector-search {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .page-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
        }
        
        .page-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f1;
            transition: background 0.1s;
        }
        
        .page-item:last-child {
            border-bottom: none;
        }
        
        .page-item:hover {
            background: #f8f9fa;
        }
        
        .page-item input[type="radio"] {
            margin-right: 10px;
        }
        
        .page-item .icon {
            margin-right: 8px;
        }
        
        .page-item .name {
            flex: 1;
            font-size: 13px;
        }
        
        .page-item .type-badge {
            font-size: 10px;
            padding: 2px 6px;
            background: #e5e5e5;
            border-radius: 3px;
            color: #666;
        }
        
        .agent-selector-modal {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }
        
        .agent-selector-modal label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .agent-selector-modal select {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
        }
        
        .collapsible {
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            margin-bottom: 10px;
            background: #fff;
        }
        
        .collapsible-header {
            padding: 14px 18px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.15s;
        }
        
        .collapsible-header:hover {
            background: #f0f0f1;
        }
        
        .collapsible-content {
            padding: 20px;
            display: none;
            border-top: 1px solid #e5e5e5;
        }
        
        .collapsible.open .collapsible-content {
            display: block;
        }
        
        .collapsible .arrow {
            color: #666;
            transition: transform 0.2s;
        }
        
        .collapsible.open .arrow {
            transform: rotate(180deg);
        }
        
        .color-picker-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-preview {
            width: 36px;
            height: 36px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* ===========================================
           Responsive adjustments
           =========================================== */
        @media (max-width: 1400px) {
            .talktopc-admin-wrap {
                max-width: 100%;
                padding: 20px;
            }
        }
        
        @media (max-width: 782px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-row label {
                width: 100%;
                padding-bottom: 5px;
            }
            
            .quick-links {
                grid-template-columns: 1fr;
            }
            
            .credits-box {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .create-agent-inline {
                flex-direction: column;
            }
            
            .create-agent-inline input {
                max-width: 100%;
            }
        }
    ';
}

/**
 * Enqueue admin styles using WordPress enqueue functions
 * 
 * WordPress Plugin Review: Uses wp_add_inline_style() instead of inline <style> tags
 */
function talktopc_enqueue_admin_styles($hook) {
    // Only load on TalkToPC admin pages
    if (strpos($hook, 'talktopc') === false) {
        return;
    }
    
    // Register dummy stylesheet handle (required for wp_add_inline_style)
    wp_register_style('talktopc-admin', false, [], TALKTOPC_VERSION);
    wp_enqueue_style('talktopc-admin');
    
    // Add inline styles
    wp_add_inline_style('talktopc-admin', talktopc_get_admin_styles_css());
}
add_action('admin_enqueue_scripts', 'talktopc_enqueue_admin_styles');

/**
 * Render admin styles (deprecated - kept for backwards compatibility)
 * 
 * @deprecated Use talktopc_enqueue_admin_styles() instead
 */
function talktopc_render_admin_styles() {
    // This function is deprecated but kept for backwards compatibility
    // Styles are now enqueued via admin_enqueue_scripts hook
}