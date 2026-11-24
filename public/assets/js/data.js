/**
 * SAMSARA EXPEDITION — Data & Configuration Module
 * Scalable & Modular dynamic configuration loaded from Laravel Backend
 */

const getLiveProfile = () => (typeof window !== 'undefined' && window.SAMSARA_EXPEDITION_PROFILE) ? window.SAMSARA_EXPEDITION_PROFILE : {};
const getLivePricing = () => (typeof window !== 'undefined' && window.SAMSARA_LIVE_PRICING) ? window.SAMSARA_LIVE_PRICING : {};
const getLiveTelemetry = () => (typeof window !== 'undefined' && window.SAMSARA_LIVE_TELEMETRY) ? window.SAMSARA_LIVE_TELEMETRY : {};

const SAMSARA_CONFIG = {
  get expedition() {
    const p = getLiveProfile();
    return {
      name: p.expedition_name || "SAMSARA EXPEDITION",
      basecampName: p.basecamp_name || "Basecamp SAMSARA",
      peakName: p.peak_name || "Puncak Triangulasi Gunung Cikuray",
      elevationMeters: p.mountain_elevation_num || 2821,
      elevationDisplay: p.mountain_elevation || "2.821 MDPL",
      location: p.basecamp_location || "Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat",
      maxDailyQuota: p.default_daily_quota ? Number(p.default_daily_quota) : 150,
      minClimbersPerGroup: p.min_climbers_per_group ? Number(p.min_climbers_per_group) : 1,
      maxClimbersPerGroup: p.max_climbers_per_group ? Number(p.max_climbers_per_group) : 15,
      adminWhatsApp: p.admin_whatsapp || "",
      adminWhatsAppDisplay: p.admin_whatsapp_display || "",
      emergencyHotline: p.emergency_hotline || "",
      sarFrequency: p.sar_frequency || "",
      bankName: p.payment_bank_name || "",
      bankAccount: p.payment_bank_account || "",
      bankAccountRaw: p.payment_bank_account_raw || "",
      bankHolder: p.payment_bank_holder || "",
      qrisNmid: p.payment_qris_nmid || "",
      qrisName: p.payment_qris_name || "",
    };
  },
  get pricing() {
    const pr = getLivePricing();
    return {
      simaksiPerPerson: pr.simaksi_fee_per_person !== undefined ? Number(pr.simaksi_fee_per_person) : 25000,
      ojekPerTrip: pr.ojek_fee_per_trip !== undefined ? Number(pr.ojek_fee_per_trip) : 30000,
      ojekBasecampShare: pr.ojek_basecamp_share !== undefined ? Number(pr.ojek_basecamp_share) : 5000,
      ojekRouteName: pr.ojek_route_name || "Basecamp SAMSARA - Pos 1 (Kebun Teh)",
      porterPerTrip: pr.porter_fee_per_trip !== undefined ? Number(pr.porter_fee_per_trip) : 350000,
      parkingMotor: pr.parking_motor_fee !== undefined ? Number(pr.parking_motor_fee) : 10000,
      parkingCar: pr.parking_car_fee !== undefined ? Number(pr.parking_car_fee) : 25000,
      trashDepositEnabled: pr.trash_deposit_enabled !== undefined ? Boolean(pr.trash_deposit_enabled) : false,
      trashDeposit: pr.trash_deposit_amount !== undefined ? Number(pr.trash_deposit_amount) : 50000,
    };
  },
  get telemetry() {
    const t = getLiveTelemetry();
    return {
      trailStatus: t.status ? (t.status.toUpperCase() === 'OPEN' ? 'TERBUKA (NORMAL)' : t.status.toUpperCase()) : "TERBUKA (NORMAL)",
      trailStatusColor: t.status === 'closed' ? "rose" : "emerald",
      pos2WaterSpring: t.pos2_water_status || "MELIMPAH (12 L/mnt)",
      pos2WaterNote: "Titik air terakhir sebelum vegetasi puncak",
      nightTemperature: t.night_temp_celsius ? `${t.night_temp_celsius}°C (Dini Hari)` : "9°C (Dini Hari)",
      soilCondition: t.soil_condition || "PADAT & KERING (Aman)",
      windSpeed: t.wind_speed || "14 km/jam Barat Daya",
      lastUpdated: t.updated_at || "Terbaru",
      sarFrequency: t.sar_frequency || "",
    };
  },
  waypoints: [
    {
      code: "POS 00",
      name: "BASECAMP SAMSARA",
      elevation: 1450,
      distance: "0.0 KM",
      duration: "START",
      waterSource: true,
      campArea: true,
      description: "Registrasi SIMAKSI, cek perlengkapan darurat, briefing ranger, & parkir kendaraan."
    },
    {
      code: "POS 01",
      name: "BATAS PERKEBUNAN TEH",
      elevation: 1780,
      distance: "+2.1 KM",
      duration: "45 MENIT",
      waterSource: false,
      campArea: false,
      description: "Batas akhir akses armada Ojek Trail. Pintu gerbang vegetasi hutan tropis primer."
    },
    {
      code: "POS 02",
      name: "MATA AIR CEMARA",
      elevation: 2150,
      distance: "+1.8 KM",
      duration: "75 MENIT",
      waterSource: true,
      campArea: false,
      criticalWarning: "PERINGATAN: Titik pengisian air minum terakhir! Wajib isi penuh perbekalan botol di sini.",
      description: "Debit air konstan 12 L/mnt sepanjang tahun. Area istirahat sejuk di bawah naungan pohon pinus."
    },
    {
      code: "POS 03",
      name: "BATU TAPAK SHELTER",
      elevation: 2420,
      distance: "+1.4 KM",
      duration: "60 MENIT",
      waterSource: false,
      campArea: false,
      description: "Tanjakan akar terjal (kemiringan 45°). Tersedia webbing tali pengaman di titik kritis."
    },
    {
      code: "POS 04",
      name: "CAMPING GROUND KAWASAN RINDANG",
      elevation: 2680,
      distance: "+1.1 KM",
      duration: "50 MENIT",
      waterSource: false,
      campArea: true,
      description: "Kapasitas 35 tenda dome. Terlindung dari angin badai punggungan barat daya. Lokasi favorit bermalam."
    },
    {
      code: "POS 05",
      name: "PUNCAK TRIANGULASI",
      elevation: (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.mountain_elevation_num) || 2821,
      distance: "+0.6 KM",
      duration: "30 MENIT",
      waterSource: false,
      campArea: true,
      description: `Titik tertinggi ${(window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.mountain_name) || "Gunung Cikuray"}. Panorama Samudra Awan 360°.`
    }
  ],
  mockTickets: {}
};
