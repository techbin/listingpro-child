# 📘 ListingPro Child Theme with Custom Listing API

This is a **custom child theme** built for the [ListingPro WordPress theme](https://themeforest.net/item/listingpro-wordpress-directory-theme/19386460). It enables additional customization and **exposes a REST API** to create or update listing items programmatically using tools like **Postman**, **curl**, or via custom scripts.

---

## 🔧 Features

- Based on ListingPro parent theme
- Allows safe theme customization (preserves changes during updates)
- Adds custom **REST API endpoints** for:
  - Creating listings
  - Updating listings

---

## 📁 Folder Structure

listingpro-child/
│
├── functions.php # API logic lives here
├── style.css
├── templates/
│ └── ... # Optional: override templates from parent theme

## 🚀 Installation

1. Upload the `listingpro-child` theme to your WordPress `wp-content/themes/` directory.
2. Activate the child theme in **WordPress Admin → Appearance → Themes**.
3. Ensure that the **ListingPro parent theme** is installed and active as a base.

---

## 🔐 Authentication

All API requests require a valid WordPress user token or basic authentication.

You can use:
- JWT Authentication plugin
- Basic Auth (for development)
- Application Passwords (WordPress 5.6+) # this script comes with Application Passwords support 

---

## 📡 API Endpoints

### 📥 Create Listing

**POST** `/wp-json/listingpro_child/v1/create-listing`

**Headers:**
```http
Authorization: Basic base64(username:password)
Content-Type: application/json
```

**Body Example:**
```json
{
  "title": "Kingston Smiles Dental",
  "description": "Holistic dental care, ethical and empathetic treatment.",
  "Categories": ["Dentists", "Dental"],
  "Locations": ["Canberra","Adelaide"],
  "Tags": ["Gentle", "Affordable"],
  "lp_fields": {
    "features": ["Wheelchair accessible","Free parking"],
    "delivery" : 0,
    "take-out" : 0,
    "delivery-mfilter" : "delivery-0",
    "take-out-mfilter" : "take-out-0"
  },
  "lp_options": {
    "plan_id": 0,
    "tagline_text": "Gentle dentistry in Kingston.",
    "gAddress": "1B/61 Giles St, Kingston ACT 2604 Australia",
    "latitude": "-35.3134583",
    "longitude": "149.144154",
    "phone": "02 6295 9333",
    "email": "reception@kingstonsmiles.com.au",
    "website": "https://www.kingstonsmiles.com.au",
    "price_status": "inexpensive",
    "business_hours": {
      "Monday": { "open": "08:30am", "close": "06:00pm" }
    }
  }
}```
**Response:**
```json
{
    "success": true,
    "url": "/listing/kingston-smiles-dental/"
}
```

🔍 Testing with Postman
Import the API URL into Postman.
Set up Basic Auth or use a JWT token.
Select POST method, set headers, and include the JSON body.
Hit Send and verify the response.

🛠 Customization
You can further customize the child theme by:
Overriding ListingPro templates in the /templates/ directory
Adding more API functionality in /includes/custom-api.php
Hooking into WordPress and ListingPro actions/filters

📄 License
This theme inherits the GPL license from WordPress and ListingPro. Custom modifications are owned by the developer.

🤝 Contributions & Support
Pull requests are welcome. If you encounter issues or would like to request features, feel free to open an issue on GitHub.

📬 Contact
For help with integration, customization, or hosting:
Email: oztechsolutions78@gmail.com or info@bucklit.com.au
Website: bucklit.com.au