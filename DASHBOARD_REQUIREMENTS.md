# Dashboard Requirements - TickTrack

> Berdasarkan `frontend_planning.md` - Ada **5 Dashboard Berbeda** berdasarkan Role

---

## 1. Dashboard Pegawai (User/Employee)
**Role:** `pegawai` | **Permission:** `view-own-ticket`

### Komponen Utama:
- **4 Stat Cards:**
  - 🎫 Total Tiket
  - 🔄 Dalam Proses
  - ⏳ Pending
  - ✅ Selesai

- **Tabel Tiket Terbaru Saya:**
  - Kolom: No. Tiket | Judul | Status | Prioritas | Waktu
  - Tombol: [+ Buat Tiket]
  - Limit: 3-5 tiket terbaru

### Karakteristik:
- Simple & fokus pada tiket user sendiri
- Quick action untuk membuat tiket baru
- Tidak ada chart/analytics

---

## 2. Dashboard Helpdesk (Tier 1)
**Role:** `helpdesk` | **Permission:** `triage-ticket`, `work-ticket`, `view-all-ticket`

### Komponen Utama:
- **5 Stat Cards dengan Trending:**
  - 🆕 New (+3 hari)
  - 🔄 Proses (↑12%)
  - ⏳ Pending (↓2)
  - ✅ Solved (+8 hari)
  - 📁 Closed (+15 hari)

- **📊 Distribusi Kategori (Donut Chart):**
  - Visualisasi persentase tiket per kategori
  - Contoh: Jaringan 35%, Hardware 28%

- **📈 Trend Tiket (Line Chart - 7 Hari):**
  - 2 lines: New vs Resolved
  - X-axis: Hari (Sen-Jum)

- **🔔 Perlu Triage (Priority Queue):**
  - List 3 tiket yang belum di-triage
  - Info: No. Tiket, Judul singkat, Prioritas, Waktu
  - Tombol: [Lihat Semua →]

- **⏱️ Aktivitas Terbaru:**
  - Timeline aktivitas real-time
  - Contoh: "5m Ahmad buat tiket baru"
  - Limit: 3-5 aktivitas terakhir

### Karakteristik:
- **Dashboard paling kompleks** dengan banyak visualisasi
- Fokus pada monitoring & triage
- Real-time updates penting

---

## 3. Dashboard Teknisi (Tier 2/IT Staff)
**Role:** `teknisi` | **Permission:** `work-ticket`

### Komponen Utama:
- **4 Stat Cards:**
  - 📥 Ditugaskan (Assigned to me)
  - 🔄 Dalam Proses
  - ⏳ Pending
  - ✅ Selesai

- **📥 Tiket Ditugaskan ke Anda:**
  - Tabel dengan kolom: No. Tiket | Judul | Prioritas | Status | Actions
  - **Action Buttons:**
    - [▶️] Mulai Kerjakan
    - [↩️] Kembalikan ke Helpdesk
  - Sortir by: Prioritas → Waktu

### Karakteristik:
- Fokus pada task management
- Action-oriented (quick start/return ticket)
- Tidak ada chart kompleks

---

## 4. Dashboard Manager TI
**Role:** `manager_ti` | **Permission:** `approve-request`, `view-reports`, `view-all-ticket`

### Komponen Utama:
- **🔔 Approval Queue Alert (Top Priority):**
  - Banner highlight: "3 permintaan menunggu"
  - Tombol: [Lihat Semua →]

- **5 Stat Cards (High-Level Metrics):**
  - 📊 Total Tiket
  - 🔓 Open Tiket
  - ✅ SLA Met (%)
  - ⏱️ Avg Response Time (jam)
  - 🔔 Pending Approval

- **📊 Performa Tim (Progress Bars):**
  - List teknisi dengan persentase produktivitas
  - Contoh: Budi (80%), Rudi (60%), Andi (40%)

- **🚨 Tiket Butuh Perhatian (Alert Box):**
  - ⚠️ 3 tiket > 24 jam tanpa update
  - ⚠️ 2 tiket pending > 3 hari
  - ⚠️ 1 tiket SLA breach

- **📈 Distribusi per Unit Kerja (Bar Chart):**
  - Horizontal bars menunjukkan volume tiket per unit
  - Contoh: Bag. Umum (40%), Lab TI (25%)

### Karakteristik:
- **Executive dashboard** - High-level overview
- Fokus pada approval & monitoring
- Alert-driven (SLA breach, pending approvals)

---

## 5. Dashboard Ketua Tim Kerja
**Role:** `ketua_tim` | **Permission:** `view-unit-ticket`

### Komponen Utama:
- **Subtitle Dynamic:**
  - "Dashboard - Unit: [Nama Unit]" (contoh: Bagian Umum)

- **4 Stat Cards (Scope: Unit Saja):**
  - 🎫 Total Tiket Tim
  - 🔄 Dalam Proses
  - ⏳ Pending
  - ✅ Selesai

- **Tiket Anggota Tim (Table):**
  - Kolom: Pelapor | No. Tiket | Judul | Status | Waktu
  - Filter: Hanya menampilkan tiket dari anggota unit yang sama

- **📊 Statistik Tim Bulan Ini:**
  - Total tiket, Persentase selesai, Avg Response Time
  - Contoh: "Total: 15 | Selesai: 8 (53%) | Avg Response: 2.5 jam"

### Karakteristik:
- Scope terbatas pada **unit kerja** saja
- Monitoring tiket anggota tim
- Summary statistik untuk reporting ke atasan

---

## Summary: Perbedaan Utama

| Dashboard | Stat Cards | Charts | Tables | Special Features |
|-----------|------------|--------|--------|------------------|
| **Pegawai** | 4 (simple) | ❌ | Tiket Saya | Quick Create Button |
| **Helpdesk** | 5 (trending) | ✅ (Donut + Line) | Triage Queue | Real-time Activity Feed |
| **Teknisi** | 4 (simple) | ❌ | Assigned Tickets | Action Buttons (Start/Return) |
| **Manager** | 5 (metrics) | ✅ (Bar) | ❌ | Approval Alert + SLA Warnings |
| **Ketua Tim** | 4 (unit scope) | ❌ | Team Tickets | Unit Filter + Team Stats |

---

## Implementasi Priority

### Phase 1 (MVP):
1. ✅ Dashboard Pegawai (paling simple)
2. ✅ Dashboard Helpdesk (paling kompleks - tapi core feature)

### Phase 2:
3. ✅ Dashboard Teknisi
4. ✅ Dashboard Ketua Tim

### Phase 3:
5. ✅ Dashboard Manager (butuh analytics lebih advanced)

---

## Technical Notes

### Component Reusability:
```
/components/dashboard/
├── StatCard.vue           # Reusable untuk semua dashboard
├── TicketTable.vue        # Shared, config per role
├── DonutChart.vue         # Helpdesk only
├── LineChart.vue          # Helpdesk only
├── BarChart.vue           # Manager only
├── ActivityFeed.vue       # Helpdesk only
├── ApprovalQueue.vue      # Manager only
└── TeamPerformance.vue    # Manager only
```

### Role Detection:
```javascript
// Di masing-masing Dashboard Page
const userRole = usePage().props.auth.user.roles[0]?.name

// Route protection
if (userRole === 'helpdesk') {
  // Show Helpdesk Dashboard
} else if (userRole === 'teknisi') {
  // Show Teknisi Dashboard
}
// ... dst
```

### API Endpoints Required:
```
GET /api/dashboard/pegawai
GET /api/dashboard/helpdesk
GET /api/dashboard/teknisi
GET /api/dashboard/manager
GET /api/dashboard/ketua-tim
```

---

**Created:** 2026-02-03  
**Based on:** frontend_planning.md (Updated: 25 Jan 2024)
