# PRODUCTS SCHEMA (FROM EXCEL)

## Excel Columns Mapping

| Excel              | Database          |
| ------------------ | ----------------- |
| Mã hàng            | sku               |
| Mã vạch            | barcode           |
| Tên hàng           | name              |
| Loại hàng          | product_type      |
| Nhóm hàng(3 cấp)   | category_path     |
| Thương hiệu        | brand             |
| Giá bán            | sale_price        |
| Giá vốn            | cost_price        |
| Tồn kho            | stock_quantity    |
| KH đặt             | reserved_quantity |
| Tồn nhỏ nhất       | min_stock         |
| Tồn lớn nhất       | max_stock         |
| ĐVT                | unit              |
| Mã ĐVT cơ bản      | base_unit_code    |
| Quy đổi            | conversion_rate   |
| Thuộc tính         | attributes (JSON) |
| Mã HH liên quan    | related_sku       |
| Hình ảnh           | images (JSON)     |
| Trọng lượng        | weight            |
| Đang kinh doanh    | is_active         |
| Được bán trực tiếp | is_direct_sale    |
| Mô tả              | description       |
| Mẫu ghi chú        | note_template     |
| Vị trí             | location          |
| Hàng thành phần    | is_combo          |
| Thời gian tạo      | created_at        |

---

## Final Table

```sql
products
- id
- sku
- barcode
- name
- product_type
- category_path
- brand

- cost_price
- sale_price

- stock_quantity
- reserved_quantity

- min_stock
- max_stock

- unit
- base_unit_code
- conversion_rate

- attributes JSON
- related_sku

- images JSON
- weight

- is_active
- is_direct_sale

- description
- note_template
- location

- is_combo

- created_at
- updated_at
```


# CUSTOMERS SCHEMA (FROM EXCEL)

## Excel Mapping

| Excel               | Database            |
| ------------------- | ------------------- |
| Mã khách hàng       | customer_code       |
| Tên khách hàng      | full_name           |
| Điện thoại          | phone               |
| Email               | email               |
| Địa chỉ             | address             |
| Phường/Xã           | ward                |
| Khu vực giao hàng   | district            |
| Loại khách          | customer_type       |
| Công ty             | company             |
| Mã số thuế          | tax_code            |
| CMND/CCCD           | identity_number     |
| Ngày sinh           | birthday            |
| Giới tính           | gender              |
| Facebook            | facebook            |
| Nhóm khách hàng     | customer_group      |
| Ghi chú             | note                |
| Người tạo           | created_by          |
| Chi nhánh tạo       | branch_created      |
| Ngày tạo            | created_at          |
| Ngày giao dịch cuối | last_transaction_at |
| Nợ cần thu          | current_debt        |
| Tổng bán            | total_spent         |
| Tổng bán trừ trả    | total_spent_net     |
| Trạng thái          | status              |

---

## Table

```sql
customers
- id
- customer_code
- full_name
- phone
- email

- address
- ward
- district

- customer_type
- company
- tax_code
- identity_number

- birthday
- gender
- facebook

- customer_group
- note

- created_by
- branch_created

- created_at
- last_transaction_at

- current_debt
- total_spent
- total_spent_net

- status
```


# INVOICES SCHEMA

## Excel Mapping

| Excel            | DB              |
| ---------------- | --------------- |
| Mã hóa đơn       | invoice_code    |
| Chi nhánh        | branch          |
| Thời gian        | created_at      |
| Người bán        | seller_name     |
| Kênh bán         | sales_channel   |
| Tổng tiền hàng   | total_amount    |
| Giảm giá hóa đơn | discount_amount |
| Khách cần trả    | final_amount    |
| Khách đã trả     | paid_amount     |
| Tiền mặt         | cash_amount     |
| Thẻ              | card_amount     |
| Ví               | wallet_amount   |
| Chuyển khoản     | transfer_amount |
| Trạng thái       | status          |

---

## Table

```sql
invoices
- id
- invoice_code
- branch
- created_at

- customer_id
- seller_name
- sales_channel

- total_amount
- discount_amount
- extra_fee

- final_amount
- paid_amount

- cash_amount
- card_amount
- wallet_amount
- transfer_amount

- status
- delivery_status
```


# INVOICE ITEMS

## Excel Mapping

| Excel      | DB               |
| ---------- | ---------------- |
| Mã hàng    | sku              |
| Tên hàng   | product_name     |
| Số lượng   | quantity         |
| Đơn giá    | unit_price       |
| Giảm giá % | discount_percent |
| Giảm giá   | discount_amount  |
| Thành tiền | final_price      |

---

## Table

```sql
invoice_items
- id
- invoice_id

- product_id
- sku
- product_name

- quantity
- unit_price

- discount_percent
- discount_amount

- final_price
```



# SHIPPING (FROM INVOICE FILE)

## Table

```sql
invoice_shipping
- id
- invoice_id

- shipping_partner
- shipping_fee

- receiver_name
- receiver_phone
- receiver_address

- delivery_time
- delivery_note
```


# RELATIONSHIPS

## ERD

customers 1 --- n invoices
invoices 1 --- n invoice_items
products 1 --- n invoice_items

---

## Laravel

Customer hasMany Invoice
Invoice belongsTo Customer

Invoice hasMany Items
Item belongsTo Invoice

Product hasMany Items
Item belongsTo Product
