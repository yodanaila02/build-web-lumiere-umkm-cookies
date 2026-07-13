# Architecture & Flow

Ringkasan singkat arsitektur, ER diagram (relasi DB) dan flow untuk panel admin.

## ER Diagram (DB relations)

```mermaid
erDiagram
    ADMINS {
        INT id PK
        VARCHAR name
        VARCHAR username
        VARCHAR password
        ENUM role
        TIMESTAMP created_at
    }
    CATEGORIES {
        INT id PK
        VARCHAR name
        VARCHAR slug
        VARCHAR description
    }
    PRODUCTS {
        INT id PK
        INT category_id FK
        VARCHAR name
        VARCHAR slug
        TEXT description
        INT price
        INT stock
        VARCHAR image
        ENUM badge
        TINYINT is_best_seller
        TINYINT is_active
    }
    ORDERS {
        INT id PK
        VARCHAR invoice_code
        VARCHAR customer_name
        VARCHAR customer_phone
        ENUM delivery_method
        VARCHAR address
        INT subtotal
        INT shipping_cost
        INT total
        ENUM status
        TIMESTAMP created_at
    }
    ORDER_ITEMS {
        INT id PK
        INT order_id FK
        INT product_id FK
        VARCHAR product_name
        INT price
        INT qty
        INT subtotal
    }
    SETTINGS {
        INT id PK
        VARCHAR setting_key
        VARCHAR setting_value
    }
    WHATSAPP_NUMBERS {
        INT id PK
        VARCHAR label
        VARCHAR number
        TINYINT is_active
        TINYINT is_primary
    }
    TESTIMONIALS {
        INT id PK
        VARCHAR customer_name
        VARCHAR content
        TINYINT rating
        TINYINT is_active
    }
    FAQS {
        INT id PK
        VARCHAR question
        VARCHAR answer
        INT sort_order
        TINYINT is_active
    }
    ARTICLES {
        INT id PK
        VARCHAR title
        VARCHAR slug
        VARCHAR excerpt
        TEXT content
        VARCHAR image
        TINYINT is_published
    }
    GALLERY {
        INT id PK
        VARCHAR title
        VARCHAR image
    }
    CONTACT_MESSAGES {
        INT id PK
        VARCHAR name
        VARCHAR email
        VARCHAR phone
        VARCHAR message
        TINYINT is_read
    }

    CATEGORIES ||--o{ PRODUCTS : "has"
    PRODUCTS ||--o{ ORDER_ITEMS : "referenced in"
    ORDERS ||--o{ ORDER_ITEMS : "contains"
    SETTINGS ||--o{ WHATSAPP_NUMBERS : "used by site (lookup)"
```

## Admin actions -> DB (operational flow)

```mermaid
flowchart TD
  Admin[Admin (authenticated)] --> Dashboard[Dashboard]
  Dashboard --> ManageProducts[Manage Products]
  Dashboard --> ManageCategories[Manage Categories]
  Dashboard --> ManageOrders[Manage Orders]
  Dashboard --> ManageContent[Manage Articles / Gallery / Testimonials / FAQs]
  Dashboard --> ManageSettings[Manage Settings & WhatsApp Numbers]
  ManageProducts -->|create/update/delete| PRODUCTS_TABLE[(products)]
  ManageCategories -->|create/update/delete| CATEGORIES_TABLE[(categories)]
  ManageOrders -->|view/update status| ORDERS_TABLE[(orders)]
  ManageOrders -->|view items| ORDER_ITEMS_TABLE[(order_items)]
  ManageContent -->|create/update/delete| ARTICLES_TABLE[(articles)]
  ManageContent -->|manage media| GALLERY_TABLE[(gallery)]
  ManageContent -->|moderate| TESTIMONIALS_TABLE[(testimonials)]
  ManageContent -->|edit FAQ| FAQS_TABLE[(faqs)]
  ManageSettings -->|edit key/value| SETTINGS_TABLE[(settings)]
  ManageSettings -->|manage numbers| WHATSAPP_TABLE[(whatsapp_numbers)]
  Login[Login Page] -->|POST creds| Auth[Authenticate via `admins` table]
  Auth -->|success| Admin
```

## Notes
- `settings` berfungsi sebagai key/value store dan diambil dengan fungsi `setting()`.
- `orders` + `order_items` menyimpan riwayat transaksi; ubah `status` tanpa mengubah items.
- Untuk referensi kode lihat `public/index.php` (routing), `src/includes/functions.php` (helper), dan folder `src/pages/admin`.

---
File ini dibuat otomatis oleh assistant untuk membantu dokumentasi proyek.
