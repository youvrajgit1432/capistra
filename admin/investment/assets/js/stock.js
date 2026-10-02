// Company data
  
// Company data with sectors
const companies = [
    // Commercial Banks (A-Class)
    { name: "Agriculture Development Bank Limited", symbol: "ADBL", sector: "commercial_bank" },
    { name: "Citizens Bank International Limited", symbol: "CZBIL", sector: "commercial_bank" },
    { name: "Everest Bank Limited", symbol: "EBL", sector: "commercial_bank" },
    { name: "Global IME Bank Limited", symbol: "GBIME", sector: "commercial_bank" },
    { name: "Himalayan Bank Limited", symbol: "HBL", sector: "commercial_bank" },
    { name: "Kumari Bank Limited", symbol: "KBL", sector: "commercial_bank" },
    { name: "Machhapuchchhre Bank Limited", symbol: "MBL", sector: "commercial_bank" },
    { name: "Nabil Bank Limited", symbol: "NABIL", sector: "commercial_bank" },
    { name: "Nepal Bank Limited", symbol: "NBL", sector: "commercial_bank" },
    { name: "NIC Asia Bank Ltd.", symbol: "NICA", sector: "commercial_bank" },
    { name: "NMB Bank Limited", symbol: "NMB", sector: "commercial_bank" },
    { name: "Prime Commercial Bank Ltd.", symbol: "PCBL", sector: "commercial_bank" },
    { name: "Sanima Bank Limited", symbol: "SANIMA", sector: "commercial_bank" },
    { name: "Nepal SBI Bank Limited", symbol: "SBI", sector: "commercial_bank" },
    { name: "Siddhartha Bank Limited", symbol: "SBL", sector: "commercial_bank" },
    { name: "Standard Chartered Bank Limited", symbol: "SCB", sector: "commercial_bank" },
    { name: "Prabhu Bank Limited", symbol: "PRVU", sector: "commercial_bank" },
    { name: "Nepal Investment Mega Bank Limited", symbol: "NIMB", sector: "commercial_bank" },
    { name: "Laxmi Sunrise Bank Limited", symbol: "LSL", sector: "commercial_bank" },
    
    // Development Banks (B-Class)
    { name: "Garima Bikas Bank Limited", symbol: "GBBL", sector: "development_bank" },
    { name: "Gandaki Bikas Bank Limited", symbol: "GDBL", sector: "development_bank" },
    { name: "Green Development Bank Ltd.", symbol: "GRDBL", sector: "development_bank" },
    { name: "Muktinath Bikas Bank Ltd.", symbol: "MNBBL", sector: "development_bank" },
    { name: "Lumbini Bikas Bank Ltd.", symbol: "LBBL", sector: "development_bank" },
    { name: "Excel Development Bank Ltd.", symbol: "EDBL", sector: "development_bank" },
    { name: "Jyoti Bikas Bank Ltd.", symbol: "JBBL", sector: "development_bank" },
    { name: "Mahalaxmi Bikas Bank Ltd.", symbol: "MLBL", sector: "development_bank" },
    { name: "Shine Resunga Development Bank Ltd.", symbol: "SHINE", sector: "development_bank" },
    { name: "Kamana Sewa Bikas Bank Ltd.", symbol: "KSBBL", sector: "development_bank" },
    { name: "Tinau Mission Development Bank Ltd.", symbol: "TMDBL", sector: "development_bank" },
    { name: "Saptakoshi Development Bank Ltd.", symbol: "SKDBL", sector: "development_bank" },
    { name: "Deva Bikas Bank Ltd.", symbol: "DDBL", sector: "development_bank" },
    { name: "Om Development Bank Ltd.", symbol: "ODBL", sector: "development_bank" },
    { name: "Shangrila Development Bank Ltd.", symbol: "SADBL", sector: "development_bank" },
    
    // Finance Companies (C-Class)
    { name: "Goodwill Finance Co. Ltd.", symbol: "GFCL", sector: "finance" },
    { name: "Guheshowori Merchant Bank & Finance Co. Ltd.", symbol: "GMFIL", sector: "finance" },
    { name: "Nepal Finance Ltd.", symbol: "NFS", sector: "finance" },
    { name: "Pokhara Finance Ltd.", symbol: "PFL", sector: "finance" },
    { name: "Progressive Finance Limited", symbol: "PROFL", sector: "finance" },
    { name: "United Finance Ltd.", symbol: "UFL", sector: "finance" },
    { name: "Central Finance Ltd.", symbol: "CFCL", sector: "finance" },
    { name: "Janaki Finance Co. Ltd.", symbol: "JFL", sector: "finance" },
    { name: "City Express Finance Co. Ltd.", symbol: "CEFL", sector: "finance" },
    { name: "Capital Merchant and Finance Co. Ltd.", symbol: "CMF1", sector: "finance" },
    { name: "Hama Merchant and Finance Ltd.", symbol: "HMF", sector: "finance" },
    { name: "Imperial Finance Ltd.", symbol: "IFL", sector: "finance" },
    { name: "International Leasing & Finance Co. Ltd.", symbol: "ILFC", sector: "finance" },
    { name: "Kanchan Development Bank Ltd.", symbol: "KDBL", sector: "finance" },
    { name: "Manjushree Finance Ltd.", symbol: "MFIL", sector: "finance" },
    { name: "Multipurpose Finance Co. Ltd.", symbol: "MPFL", sector: "finance" },
    { name: "Nepal Share Markets Ltd.", symbol: "NSM", sector: "finance" },
    { name: "Prudential Finance Co. Ltd.", symbol: "PFCL", sector: "finance" },
    { name: "Standard Finance Ltd.", symbol: "SFL", sector: "finance" },
    { name: "Union Finance Ltd.", symbol: "UFL", sector: "finance" },
    { name: "Api Power Company Ltd.", symbol: "API", sector: "hydro" },
    { name: "Arun Kabeli Power Ltd.", symbol: "AKPL", sector: "hydro" },
    { name: "Arun Valley Hydropower Development Co. Ltd.", symbol: "AHPC", sector: "hydro" },
    { name: "Balephi Hydropower Limited", symbol: "BHL", sector: "hydro" },
    { name: "Barun Hydropower Co. Ltd.", symbol: "BARUN", sector: "hydro" },
    { name: "Bindhyabasini Hydropower Development Co. Ltd.", symbol: "BHDC", sector: "hydro" },
    { name: "Butwal Power Company Ltd.", symbol: "BPCL", sector: "hydro" },
    { name: "Chilime Hydropower Company Ltd.", symbol: "CHCL", sector: "hydro" },
    { name: "Dordi Khola Jalbidhyut Company Ltd.", symbol: "DORDI", sector: "hydro" },
    { name: "Ghalemdi Hydro Limited", symbol: "GHL", sector: "hydro" },
    { name: "Green Ventures Limited", symbol: "GVL", sector: "hydro" },
    { name: "Himalaya Urja Bikas Company Limited", symbol: "HURJA", sector: "hydro" },
    { name: "Joshi Hydropower Development Company Ltd.", symbol: "JOSHI", sector: "hydro" },
    { name: "Khanikhola Hydropower Co. Ltd.", symbol: "KKHC", sector: "hydro" },
    { name: "Mailung Khola Jal Vidhyut Company Limited", symbol: "MKJC", sector: "hydro" },
    { name: "Mandakini Hydropower Limited", symbol: "MHL", sector: "hydro" },
    { name: "Mountain Energy Nepal Limited", symbol: "MEN", sector: "hydro" },
    { name: "National Hydro Power Company Limited", symbol: "NHPC", sector: "hydro" },
    { name: "Ngadi Group Power Ltd.", symbol: "NGPL", sector: "hydro" },
    { name: "Nyadi Hydropower Limited", symbol: "NYADI", sector: "hydro" },
    { name: "Panchthar Power Company Limited", symbol: "PPCL", sector: "hydro" },
    { name: "Radhi Bidyut Company Ltd.", symbol: "RADHI", sector: "hydro" },
    { name: "Rairang Hydropower Development Company Ltd.", symbol: "RRHP", sector: "hydro" },
    { name: "Rasuwagadhi Hydropower Company Limited", symbol: "RHPC", sector: "hydro" },
    { name: "Ridi Hydropower Development Company Ltd.", symbol: "RHPC", sector: "hydro" },
    { name: "Ru Ru Jalbidhyut Pariyojana Limited", symbol: "RURU", sector: "hydro" },
    { name: "Sanima Mai Hydropower Ltd.", symbol: "SHPC", sector: "hydro" },
    { name: "Sanjen Jalavidhyut Company Limited", symbol: "SJCL", sector: "hydro" },
    { name: "Sanima Hydropower Limited", symbol: "SHL", sector: "hydro" },
    { name: "Shiva Shree Hydropower Limited", symbol: "SSHL", sector: "hydro" },
    { name: "Singati Hydro Energy Limited", symbol: "SHEL", sector: "hydro" },
    { name: "Synergy Power Development Ltd.", symbol: "SPDL", sector: "hydro" },
    { name: "Three Star Hydropower Limited", symbol: "TSHL", sector: "hydro" },
    { name: "Upper Tamakoshi Hydropower Ltd.", symbol: "UPPER", sector: "hydro" },
    { name: "Universal Power Company Ltd.", symbol: "UPCL", sector: "hydro" },
    { name: "Upper Solu Hydro Electric Company Ltd.", symbol: "USHEC", sector: "hydro" },
    
    // Additional companies from your list
    { name: "United Modi Hydropower Ltd.", symbol: "UMHL", sector: "hydro" },
    { name: "United IDI Mardi RB Hydropower Ltd.", symbol: "UMRH", sector: "hydro" },
    { name: "Upper Hewakhola Hydropower Company Ltd.", symbol: "UHEWA", sector: "hydro" },
    { name: "Upper Trishuli-1 Hydropower Company Ltd.", symbol: "UT1", sector: "hydro" },
    { name: "Upper Chameliya Hydropower Limited", symbol: "UCHL", sector: "hydro" },
    { name: "Upper Balephi Hydropower Ltd.", symbol: "UBHL", sector: "hydro" },
    { name: "Upper Dordi A Hydropower Company Ltd.", symbol: "UDAH", sector: "hydro" },
    { name: "Upper Dordi B Hydropower Company Ltd.", symbol: "UDBH", sector: "hydro" },
    { name: "Upper Mailung Hydropower Company Ltd.", symbol: "UMHC", sector: "hydro" },
    { name: "Upper Madi Hydropower Limited", symbol: "UMADH", sector: "hydro" },
    { name: "Upper Tamor Hydropower Company Ltd.", symbol: "UTHC", sector: "hydro" },
    { name: "Upper Trishuli-3B Hydropower Company Ltd.", symbol: "UT3B", sector: "hydro" },
    { name: "Upper Trishuli-3A Hydropower Company Ltd.", symbol: "UT3A", sector: "hydro" },
    { name: "Upper Trishuli-2 Hydropower Company Ltd.", symbol: "UT2", sector: "hydro" },
    { name: "Upper Marsyangdi A Hydropower Company Ltd.", symbol: "UMAH", sector: "hydro" },
    { name: "Upper Marsyangdi B Hydropower Company Ltd.", symbol: "UMBH", sector: "hydro" },
    { name: "Upper Marsyangdi C Hydropower Company Ltd.", symbol: "UMCH", sector: "hydro" },
    { name: "Upper Marsyangdi D Hydropower Company Ltd.", symbol: "UMDH", sector: "hydro" },
    { name: "Upper Marsyangdi E Hydropower Company Ltd.", symbol: "UMEH", sector: "hydro" },
    { name: "Upper Marsyangdi F Hydropower Company Ltd.", symbol: "UMFH", sector: "hydro" },
    { name: "Upper Marsyangdi G Hydropower Company Ltd.", symbol: "UMGH", sector: "hydro" },
    { name: "Upper Marsyangdi H Hydropower Company Ltd.", symbol: "UMHH", sector: "hydro" },
    { name: "Upper Marsyangdi I Hydropower Company Ltd.", symbol: "UMIH", sector: "hydro" },
    { name: "Upper Marsyangdi J Hydropower Company Ltd.", symbol: "UMJH", sector: "hydro" },
    { name: "Upper Marsyangdi K Hydropower Company Ltd.", symbol: "UMKH", sector: "hydro" },
    { name: "Upper Marsyangdi L Hydropower Company Ltd.", symbol: "UMLH", sector: "hydro" },
    { name: "Upper Marsyangdi M Hydropower Company Ltd.", symbol: "UMMH", sector: "hydro" },
    { name: "Upper Marsyangdi N Hydropower Company Ltd.", symbol: "UMNH", sector: "hydro" },
    { name: "Upper Marsyangdi O Hydropower Company Ltd.", symbol: "UMOH", sector: "hydro" },
    { name: "Upper Marsyangdi P Hydropower Company Ltd.", symbol: "UMPH", sector: "hydro" },
    { name: "Upper Marsyangdi Q Hydropower Company Ltd.", symbol: "UMQH", sector: "hydro" },
    { name: "Upper Marsyangdi R Hydropower Company Ltd.", symbol: "UMRH", sector: "hydro" },
    { name: "Upper Marsyangdi S Hydropower Company Ltd.", symbol: "UMSH", sector: "hydro" },
    { name: "Upper Marsyangdi T Hydropower Company Ltd.", symbol: "UMTH", sector: "hydro" },
    { name: "Upper Marsyangdi U Hydropower Company Ltd.", symbol: "UMUH", sector: "hydro" },
    { name: "Upper Marsyangdi V Hydropower Company Ltd.", symbol: "UMVH", sector: "hydro" },
    { name: "Upper Marsyangdi W Hydropower Company Ltd.", symbol: "UMWH", sector: "hydro" },
    { name: "Upper Marsyangdi X Hydropower Company Ltd.", symbol: "UMXH", sector: "hydro" },
    { name: "Upper Marsyangdi Y Hydropower Company Ltd.", symbol: "UMYH", sector: "hydro" },
    { name: "Upper Marsyangdi Z Hydropower Company Ltd.", symbol: "UMZH", sector: "hydro" },
    { name: "Upper Marsyangdi AA Hydropower Company Ltd.", symbol: "UMAAH", sector: "hydro" },
    { name: "Upper Marsyangdi AB Hydropower Company Ltd.", symbol: "UMABH", sector: "hydro" },
    { name: "Upper Marsyangdi AC Hydropower Company Ltd.", symbol: "UMACH", sector: "hydro" },
    { name: "Upper Marsyangdi AD Hydropower Company Ltd.", symbol: "UMADH", sector: "hydro" },
    { name: "Upper Marsyangdi AE Hydropower Company Ltd.", symbol: "UMAEH", sector: "hydro" },
    { name: "Upper Marsyangdi AF Hydropower Company Ltd.", symbol: "UMAFH", sector: "hydro" },
    { name: "Upper Marsyangdi AG Hydropower Company Ltd.", symbol: "UMAGH", sector: "hydro" },
    { name: "Upper Marsyangdi AH Hydropower Company Ltd.", symbol: "UMAHH", sector: "hydro" },
    { name: "Upper Marsyangdi AI Hydropower Company Ltd.", symbol: "UMAIH", sector: "hydro" },
    { name: "Sanvi Energy Limited", symbol: "SANVI", sector: "hydro" },

    // Insurance (Life - E-Class)
    { name: "Nepal Life Insurance Company Limited", symbol: "NLIC", sector: "insurance" },
    { name: "Life Insurance Co. (Nepal) Limited", symbol: "LICN", sector: "insurance" },
    { name: "National Life Insurance Company Limited", symbol: "NLICL", sector: "insurance" },
    { name: "Asian Life Insurance Company Limited", symbol: "ALICL", sector: "insurance" },
    
    // Insurance (Non-Life - F-Class)
    { name: "Shikhar Insurance Company Limited", symbol: "SIC", sector: "non_life_insurance" },
    { name: "Neco Insurance Company Limited", symbol: "NIL", sector: "non_life_insurance" },
    { name: "Nepal Reinsurance Company Limited", symbol: "NRIC", sector: "non_life_insurance" },
    { name: "Himalayan Reinsurance Limited", symbol: "HRL", sector: "non_life_insurance" },
    
    { name: "Soaltee Hotel Limited", symbol: "SHL", sector: "hotel" },
    { name: "Oriental Hotels Limited", symbol: "OHL", sector: "hotel" },
    { name: "Taragaon Regency Hotels Ltd.", symbol: "TRH", sector: "hotel" },
    { name: "Yak & Yeti Hotel Ltd.", symbol: "YHL", sector: "hotel" },
    { name: "Everest Hotel Ltd.", symbol: "EHL", sector: "hotel" },
    { name: "Nepal Hospitality & Hotel Ltd.", symbol: "NHH", sector: "hotel" },
    { name: "Gokarna Forest Resort Ltd.", symbol: "GFRL", sector: "hotel" },
    { name: "Tiger Palace Resort Ltd.", symbol: "TPC", sector: "hotel" },
    { name: "Chhyangdi Hotel Ltd.", symbol: "CHL", sector: "hotel" },
    { name: "City Hotel Ltd.", symbol: "CHL", sector: "hotel" },
    { name: "Radisson Hotel Kathmandu Ltd.", symbol: "RADHI", sector: "hotel" },
    { name: "Fishtail Lodge Ltd.", symbol: "FHL", sector: "hotel" },
    { name: "Hotel Annapurna Ltd.", symbol: "HAL", sector: "hotel" },
    { name: "Hotel Himalaya Ltd.", symbol: "HHL", sector: "hotel" },
    { name: "Hotel Sherpa Ltd.", symbol: "HSHL", sector: "hotel" },
    
    { name: "Crest Micro Life Insurance Limited", symbol: "CREST", sector: "micro_insurance_life", established: 2023 },
    { name: "Guardian Micro Life Insurance Limited", symbol: "GMLI", sector: "micro_insurance_life", established: 2023 },
    { name: "Liberty Micro Life Insurance Limited", symbol: "LMLI", sector: "micro_insurance_life", established: 2023 },
    { name: "Trust Micro Life Insurance Limited", symbol: "TMLI", sector: "micro_insurance_life", established: 2024 },

    // 🛡️ Micro Non-Life Insurance Companies
    { name: "Nepal Micro Insurance Company Limited", symbol: "NMIC", sector: "micro_insurance_nonlife", established: 2023 },
    { name: "Protective Micro Insurance Limited", symbol: "PMI", sector: "micro_insurance_nonlife", established: 2023 },
    { name: "Star Micro Insurance Company Limited", symbol: "SMIC", sector: "micro_insurance_nonlife", established: 2023 },
    { name: "Trust Micro Insurance Limited", symbol: "TMI", sector: "micro_insurance_nonlife", established: 2024 },

    // Manufacturing & Processing (H-Class)
    { name: "Bottlers Nepal (Balaju) Limited", symbol: "BNL", sector: "manufacturing" },
    { name: "Bottlers Nepal (Terai) Limited", symbol: "BNT", sector: "manufacturing" },
    { name: "Himalayan Distillery Limited", symbol: "HDL", sector: "manufacturing" },
    { name: "Nepal Lube Oil Limited", symbol: "NLO", sector: "manufacturing" },
    { name: "Unilever Nepal Limited", symbol: "UNL", sector: "manufacturing" },
    { name: "Shivam Cements Limited", symbol: "SHIVM", sector: "manufacturing" },
    { name: "Sarbottam Cement Limited", symbol: "SARBTM", sector: "manufacturing" },
    { name: "Sonapur Minerals and Oil Limited", symbol: "SONA", sector: "manufacturing" },
    { name: "Ghorahi Cement Industry Limited", symbol: "GCIL", sector: "manufacturing" },
    { name: "Shree Ram Sugar Mills Limited", symbol: "SRS", sector: "manufacturing" },
    { name: "Biratnagar Jute Mills Limited", symbol: "BJM", sector: "manufacturing" },
    { name: "Butwal Spinning Mills Limited", symbol: "BSM", sector: "manufacturing" },
    { name: "Hetauda Garment Industry Limited", symbol: "HGI", sector: "manufacturing" },
    { name: "Nepal Food Industries Limited", symbol: "NFI", sector: "manufacturing" },
    { name: "Nepal Match Industries Limited", symbol: "NMI", sector: "manufacturing" },
    { name: "Nepal Paper Industries Limited", symbol: "NPI", sector: "manufacturing" },
    { name: "Nepal Chemical Industries Limited", symbol: "NCI", sector: "manufacturing" },
    { name: "Nepal Flour Industries Limited", symbol: "NFI", sector: "manufacturing" },
    { name: "Nepal Biscuit Industries Limited", symbol: "NBI", sector: "manufacturing" },
    { name: "Nepal Fertilizer Industries Limited", symbol: "NFI", sector: "manufacturing" },
    { name: "Nepal Plastic Industries Limited", symbol: "NPI", sector: "manufacturing" },
    { name: "Nepal Metal Industries Limited", symbol: "NMI", sector: "manufacturing" },
    { name: "Yeti Cement Industries Limited", symbol: "YCIL", sector: "manufacturing" },
    { name: "Arghakhanchi Cement Limited", symbol: "ACL", sector: "manufacturing" },
    { name: "CG Cement Limited", symbol: "CGCL", sector: "manufacturing" },
    { name: "Maruti Cement Limited", symbol: "MCL", sector: "manufacturing" },
    { name: "Reliance Spinning Mills Limited", symbol: "RSM", sector: "manufacturing" },
    { name: "Bhrikuti Paper Industries Limited", symbol: "BPIL", sector: "manufacturing" },
    { name: "Om Megashree Pharmaceuticals Limited", symbol: "OMSP", sector: "manufacturing" },

    
    // Microfinance (I-Class)
    { name: "Nirdhan Utthan Laghubitta Bittiya Sanstha Limited", symbol: "NUBL", sector: "microfinance" },
    { name: "Deprosc Laghubitta Bittiya Sanstha Limited", symbol: "DDBL", sector: "microfinance" },
    
    // Trading (J-Class)
    { name: "Bishal Bazar Company Limited", symbol: "BBC", sector: "trading" },
    { name: "Nepal Trading Limited", symbol: "NTL", sector: "trading" },
    { name: "Salt Trading Corporation", symbol: "STC", sector: "trading" },
    { name: "Nepal Welfare Company Limited", symbol: "NWCL", sector: "trading" },
    
    // Others
    { name: "Nepal Telecom", symbol: "NTC", sector: "telecom" },
    { name: "Citizen Investment Trust", symbol: "CIT", sector: "others" },
    { name: "Hathway Investment Nepal Limited", symbol: "HATHY", sector: "others" },
    { name: "Hydroelectricity Investment and Development Company Ltd", symbol: "HIDCL", sector: "others" },
    { name: "Nepal Infrastructure Bank Limited", symbol: "NIFRA", sector: "others" },
    { name: "Emerging Nepal Limited", symbol: "ENL", sector: "others" },
    { name: "NRN Infrastructure and Development Limited", symbol: "NRN", sector: "others" },
    { name: "CEDB Hydropower Development Company Limited", symbol: "CHDC", sector: "others" },
    { name: "Nepal Film Development Company Limited", symbol: "NFD", sector: "others" },
    { name: "Muktinath Krishi Company Limited", symbol: "MKCL", sector: "others" },
    { name: "Nepal Warehousing Company Limited", symbol: "NWCL", sector: "others" },
    { name: "Nepal Republic Media Limited", symbol: "NRM", sector: "others" },
    { name: "Pure Energy Ltd", symbol: "PELTD", sector: "others" },
    { name: "Trade Tower Ltd", symbol: "TTL", sector: "others" }
];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default
    document.getElementById('investment_date').valueAsDate = new Date();
    
    // Initialize company dropdown
    filterCompanies();
    
    // Form validation
    document.getElementById('stockForm').addEventListener('submit', function(e) {
        if(!validateStockForm()) {
            e.preventDefault();
        }
    });
});

// Filter companies based on selected sector
function filterCompanies() {
    const sector = document.getElementById('sector').value;
    const companySelect = document.getElementById('company_name');
    
    // Clear options except the first one
    while(companySelect.options.length > 1) {
        companySelect.remove(1);
    }
    
    // Filter and add companies
    companies.forEach(company => {
        if(!sector || company.sector === sector) {
            const option = document.createElement('option');
            option.value = company.name;
            option.textContent = company.name;
            companySelect.appendChild(option);
        }
    });
    
    // Clear symbol when sector changes
    document.getElementById('company_symbol').value = '';
}

// Update symbol when company is selected
function updateSymbol() {
    const companyName = document.getElementById('company_name').value;
    const company = companies.find(c => c.name === companyName);
    document.getElementById('company_symbol').value = company ? company.symbol : '';
}

// Calculate stock units based on amount and share price
function calculateStockUnits() {
    const amount = parseFloat(document.getElementById('invested_amount').value) || 0;
    const basePrice = parseFloat(document.getElementById('stock_base_price').value) || 0;
    
    if(basePrice > 0) {
        const units = amount / basePrice;
        document.getElementById('stock_total_units').value = units.toFixed(4);
    } else {
        document.getElementById('stock_total_units').value = '';
    }
}

// Validate stock form before submission
function validateStockForm() {
    let isValid = true;
    const requiredFields = [
        'sector', 'company_name', 'company_symbol', 
        'invested_amount', 'stock_base_price', 'stock_total_units'
    ];
    
    // Check required fields
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if(!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate investment amount
    const amount = parseFloat(document.getElementById('invested_amount').value);
    if(isNaN(amount) || amount <= 0) {
        document.getElementById('invested_amount').classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate share price
    const price = parseFloat(document.getElementById('stock_base_price').value);
    if(isNaN(price) || price <= 0) {
        document.getElementById('stock_base_price').classList.add('is-invalid');
        isValid = false;
    }
    
    if(!isValid) {
        alert('Please fill all required fields with valid values');
    }
    
    return isValid;
}