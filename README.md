# Aviritech Opera Sync Engine

**A professional integration layer connecting OPERA PMS (On-Premise) to modern web systems**


## 🚀 Overview

The **Aviritech Opera Sync Engine** is a robust middleware solution designed to bridge the gap between legacy **OPERA PMS (on-premise)** systems and modern digital platforms such as websites, booking engines, and automation tools.

It enables hotels to synchronize critical data like room availability and reservations in near real-time—without disrupting their existing OPERA infrastructure.


## 🎯 Key Features

* 🔄 **Automated Data Synchronization**

  * Room availability
  * Reservations
  * Rate information

* 🌐 **Website Integration Ready**

  * Power real-time booking engines
  * Eliminate manual updates

* 📥 **Secure Booking Ingestion**

  * Pull bookings from external platforms
  * Queue-based processing for safety

* 🛡️ **Fail-Safe Architecture**

  * Local queue system prevents data loss
  * Retry mechanisms for failed operations

* ⚙️ **Lightweight Deployment**

  * Runs on existing hotel server
  * Minimal infrastructure requirements


## 🏗️ Architecture

```
OPERA PMS Database (Local)
        ↓
Aviritech Sync Agent (PHP)
        ↓
Aviritech Cloud API
        ↓
Website / Booking Engine / Automation Tools
```


## 🔧 Technology Stack

* **Backend:** PHP (CLI-based agent)
* **Database Connectivity:** Oracle (OCI8) / SQL Server
* **Communication:** REST API (cURL)
* **Scheduling:** Cron Jobs
* **Data Handling:** JSON Queue System


## 📦 Project Structure

```
/sync-agent
│── config.php
│── db.php
│── functions.php
│── sync_availability.php
│── sync_reservations.php
│── pull_bookings.php
│── process_queue.php
│── /queue
│── /logs
```

## ⚙️ Installation

### 1. Clone Repository

```
git clone https://github.com/aviritech/opera-sync-engine.git
cd opera-sync-engine
```


### 2. Configure Environment

Edit `config.php`:

* Database credentials
* API endpoint
* Authentication token


### 3. Setup Directories

```
mkdir queue logs
chmod 755 queue logs
```

### 4. Configure Cron Jobs

```
* * * * * php /path/sync_availability.php
* * * * * php /path/sync_reservations.php
* * * * * php /path/pull_bookings.php
* * * * * php /path/process_queue.php
```

## 🔐 Security Considerations

* All API communication uses **secure tokens**
* Sensitive data should be transmitted over **HTTPS**
* Database credentials must be protected
* Logging system tracks errors and anomalies


## ⚠️ Important Notes

* OPERA PMS database schemas vary by installation
* Table and column mappings must be customized per client
* Direct database writes should be handled with caution
* Always test on staging before production deployment


## 📈 Use Cases

* Hotel website booking integration
* Real-time availability display
* Reservation synchronization
* WhatsApp booking automation
* Centralized hotel data management


## 💼 Business Value

This solution enables hotels to:

* Increase direct bookings
* Reduce manual operations
* Eliminate double-bookings
* Modernize legacy systems without replacement


## 🧠 Aviritech Advantage

Aviritech specializes in building **practical, scalable integrations** for businesses operating on legacy systems.

The Opera Sync Engine is not just a tool—it's a **foundation for digital transformation in hospitality**.


## 📞 Support & Customization

For enterprise deployment, customization, or support:

**Aviritech Team**
Transforming Businesses through Innovative Technology


## 📄 License

Proprietary – Developed and maintained by Aviritech.
Usage subject to agreement.


## 🚀 Future Enhancements

* Real-time sync via event triggers
* Admin dashboard & analytics
* Multi-property support
* AI-powered booking automation
* Channel manager integrations



**Built for reliability. Designed for scale. Engineered by Aviritech.**

**By Engineer JULIUS RAPHAEL OCHAI**
