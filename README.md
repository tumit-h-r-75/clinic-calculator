# DermaScope ROI Calculator Plugin

An interactive WordPress plugin for dermatology clinics to calculate monthly revenue, profit, break-even treatments, and ROI from configurable calculator posts.

## Features

- Custom `calculator` post type.
- Admin meta boxes for clinic name, pricing ranges, default price/material values, working days, and two lease options.
- Auto-generated shortcode per calculator.
- Standalone price shortcodes for leasing and rental price boxes.
- Responsive frontend calculator with live slider and dropdown calculations.
- Optional calculations tracking table scaffold for future reporting.

## Installation

1. Place this folder in `/wp-content/plugins/your-dermascope-roi-calculator/`.
2. Activate **DermaScope ROI Calculator** from WordPress admin.
3. Go to **Calculators -> Add New**.
4. Configure settings and publish.
5. Copy the generated shortcode into any page or post.

Example:

```text
[derma_calculator id="derma_calculator_123_abc123"]
```

Standalone product prices:

```text
[derma_calculator_price id="derma_calculator_123_abc123" type="leasing"]
[derma_calculator_price id="derma_calculator_123_abc123" type="rental"]
```

## Calculation Formulas

- Total treatments = treatments per day * working days.
- Gross revenue = total treatments * price per session.
- Net profit = gross revenue - material costs - lease cost.
- Break-even = lease cost / (price per session - material cost).
- ROI = net profit / lease cost.

## Requirements

- WordPress 5.0 or newer.
- PHP 7.4 or newer.
