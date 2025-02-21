const ApiVersion = '/api/v1.0'
const ApiRoutes = {
    // Authentication
    Login: ApiVersion + '/auth/login',
    Logout: ApiVersion + '/auth/logout',
    Register: ApiVersion + '/auth/register',
    ForgotPassword: ApiVersion + '/auth/forgot',
    ResetPassword: ApiVersion + '/auth/reset/password',
    //Accounts
    CategoryList: ApiVersion + '/category/list',
    CategoryParent: ApiVersion + '/category/parent',
    CategorySave: ApiVersion + '/category/save',
    CategorySingle: ApiVersion + '/category/single',
    CategoryUpdate: ApiVersion + '/category/update',
    //Transaction
    TransactionSave: ApiVersion + '/transaction/save',
    TransactionSingle: ApiVersion + '/transaction/single',
    TransactionSplit: ApiVersion + '/transaction/split',
    //Balance Sheet
    BalanceSheetGet: ApiVersion + '/balance-sheet/get',
    //Profit and loss
    ProfitLossGet: ApiVersion + '/profit-and-loss/get',
    //Income statement
    IncomeStatement: ApiVersion + '/income-statement/get',
    //Account payable
    PayableGet: ApiVersion + '/payable/get',
    //Account receivable
    ReceivableGet: ApiVersion + '/receivable/get',
    //Trail Balance
    TrailBalanceGet: ApiVersion + '/trail-balance/get',
    //Ledger
    LedgerGet: ApiVersion + '/ledger/get',
    //Product
    ProductAdd: ApiVersion + '/product/save',
    ProductEdit: ApiVersion + '/product/update',
    ProductDelete: ApiVersion + '/product/delete',
    ProductList: ApiVersion + '/product/list',
    ProductSingle: ApiVersion + '/product/single',
    ProductType: ApiVersion + '/product/type/list',
    ProductDispenser: ApiVersion + '/product/dispenser',
    ProductTank: ApiVersion + '/product/get/tank',
    //Dispenser
    DispenserAdd: ApiVersion + '/dispenser/save',
    DispenserEdit: ApiVersion + '/dispenser/update',
    DispenserDelete: ApiVersion + '/dispenser/delete',
    DispenserList: ApiVersion + '/dispenser/list',
    DispenserSingle: ApiVersion + '/dispenser/single',
    //Dispenser Reading
    DispenserReadingAdd: ApiVersion + '/dispenser/reading/save',
    DispenserReadingEdit: ApiVersion + '/dispenser/reading/update',
    DispenserReadingDelete: ApiVersion + '/dispenser/reading/delete',
    DispenserReadingList: ApiVersion + '/dispenser/reading/list',
    DispenserReadingSingle: ApiVersion + '/dispenser/reading/single',
    //Nozzle
    NozzleAdd: ApiVersion + '/nozzle/save',
    NozzleEdit: ApiVersion + '/nozzle/update',
    NozzleDelete: ApiVersion + '/nozzle/delete',
    NozzleList: ApiVersion + '/nozzle/list',
    NozzleSingle: ApiVersion + '/nozzle/single',
    //Nozzle Reading
    NozzleReadingAdd: ApiVersion + '/nozzle/reading/save',
    NozzleReadingEdit: ApiVersion + '/nozzle/reading/update',
    NozzleReadingDelete: ApiVersion + '/nozzle/reading/delete',
    NozzleReadingList: ApiVersion + '/nozzle/reading/list',
    NozzleReadingSingle: ApiVersion + '/nozzle/reading/single',
    //Shift Sale
    ShiftSale: ApiVersion + '/shift/sale',
    ShiftSaleAdd: ApiVersion + '/shift/sale/save',
    ShiftSaleEdit: ApiVersion + '/shift/sale/update',
    ShiftSaleDelete: ApiVersion + '/shift/sale/delete',
    ShiftSaleList: ApiVersion + '/shift/sale/list',
    ShiftSaleSingle: ApiVersion + '/shift/sale/single',
    ShiftSaleGetCategory: ApiVersion + '/shift/sale/getCategory',
    GetShiftByDate: ApiVersion + '/shift/sale/getShiftByDate',
    //expense
    ExpenseAdd: ApiVersion + '/expense/save',
    ExpenseEdit: ApiVersion + '/expense/update',
    ExpenseDelete: ApiVersion + '/expense/delete',
    ExpenseList: ApiVersion + '/expense/list',
    ExpenseSingle: ApiVersion + '/expense/single',
    ExpenseApprove: ApiVersion + '/expense/approve',
    ExpenseReport: ApiVersion + '/expense/report',
    Expense: ApiVersion + '/expense',
    //Tank
    TankAdd: ApiVersion + '/tank/save',
    TankEdit: ApiVersion + '/tank/update',
    TankDelete: ApiVersion + '/tank/delete',
    TankList: ApiVersion + '/tank/list',
    TankSingle: ApiVersion + '/tank/single',
    TankGetNozzle: ApiVersion + '/tank/get/nozzle',
    TankByProduct: ApiVersion + '/tank/byProduct',
    TankBstiChart: ApiVersion + '/tank/getBstiChart',
    TankGetVolume: ApiVersion + '/tank/getVolume',
    //Tank Reading
    TankReadingAdd: ApiVersion + '/tank/reading/save',
    TankReadingEdit: ApiVersion + '/tank/reading/update',
    TankReadingDelete: ApiVersion + '/tank/reading/delete',
    TankReadingList: ApiVersion + '/tank/reading/list',
    TankReadingSingle: ApiVersion + '/tank/reading/single',
    TankReadingLatest: ApiVersion + '/tank/reading/latest',
    TankWithLatestReading: ApiVersion + '/tank/tankWithLatestReading',
    //Tank Refill
    TankRefillAdd: ApiVersion + '/tank/refill/save',
    TankRefillEdit: ApiVersion + '/tank/refill/update',
    TankRefillDelete: ApiVersion + '/tank/refill/delete',
    TankRefillList: ApiVersion + '/tank/refill/list',
    TankRefillSingle: ApiVersion + '/tank/refill/single',
    //Bank
    BankAdd: ApiVersion + '/bank/save',
    BankEdit: ApiVersion + '/bank/update',
    BankDelete: ApiVersion + '/bank/delete',
    BankList: ApiVersion + '/bank/list',
    BankSingle: ApiVersion + '/bank/single',
    //Entity
    EntityAdd: ApiVersion + '/loan-entity/save',
    EntityEdit: ApiVersion + '/loan-entity/update',
    EntityDelete: ApiVersion + '/loan-entity/delete',
    EntityList: ApiVersion + '/loan-entity/list',
    EntitySingle: ApiVersion + '/loan-entity/single',
    //Vendor
    VendorAdd: ApiVersion + '/vendor/save',
    VendorEdit: ApiVersion + '/vendor/update',
    VendorDelete: ApiVersion + '/vendor/delete',
    VendorList: ApiVersion + '/vendor/list',
    VendorSingle: ApiVersion + '/vendor/single',
    VendorPayment: ApiVersion + '/vendor/payment',
    VendorReport: ApiVersion + '/vendor/report',
    //Pay order
    PayOrder: ApiVersion + '/pay/order',
    PayOrderAdd: ApiVersion + '/pay/order/save',
    PayOrderEdit: ApiVersion + '/pay/order/update',
    PayOrderDelete: ApiVersion + '/pay/order/delete',
    PayOrderList: ApiVersion + '/pay/order/list',
    PayOrderSingle: ApiVersion + '/pay/order/single',
    PayOrderLatest: ApiVersion + '/pay/order/latest',
    PayOrderQuantity: ApiVersion + '/pay/order/quantity',
    //sale
    DummySaleAdd: ApiVersion + '/dummySale/save',
    DummySaleSingle: ApiVersion + '/dummySale/single',
    DummySaleList: ApiVersion + '/dummySale/list',
    SaleAdd: ApiVersion + '/sale/save',
    SaleEdit: ApiVersion + '/sale/update',
    SaleSingle: ApiVersion + '/sale/single',
    SaleDelete: ApiVersion + '/sale/delete',
    SaleList: ApiVersion + '/sale/list',
    UnauthorizedBill: ApiVersion + '/sale/unauthorizedBill',
    UnauthorizedBillTransfer: ApiVersion + '/sale/unauthorizedBill/transfer',
    //Credit Company
    CreditCompanyAdd: ApiVersion + '/creditCompany/save',
    CreditCompanyEdit: ApiVersion + '/creditCompany/update',
    CreditCompanySingle: ApiVersion + '/creditCompany/single',
    CreditCompanyDelete: ApiVersion + '/creditCompany/delete',
    CreditCompanyList: ApiVersion + '/creditCompany/list',
    //POS Machine
    posMachineAdd: ApiVersion + '/posMachine/save',
    posMachineEdit: ApiVersion + '/posMachine/update',
    posMachineSingle: ApiVersion + '/posMachine/single',
    posMachineDelete: ApiVersion + '/posMachine/delete',
    posMachineList: ApiVersion + '/posMachine/list',
    //Employee
    employeeAdd: ApiVersion + '/employee/save',
    employeeEdit: ApiVersion + '/employee/update',
    employeeSingle: ApiVersion + '/employee/single',
    employeeDelete: ApiVersion + '/employee/delete',
    employeeList: ApiVersion + '/employee/list',
    //Salary
    salarySearchEmployee: ApiVersion + '/salary/searchEmployee',
    salaryAdd: ApiVersion + '/salary/save',
    salaryEdit: ApiVersion + '/salary/update',
    salarySingle: ApiVersion + '/salary/single',
    salaryDelete: ApiVersion + '/salary/delete',
    salaryList: ApiVersion + '/salary/list',
    salaryGetCategory: ApiVersion + '/salary/getCategory',
    salaryPrint: ApiVersion + '/salary/print',
    //Company sale
    companySaleAdd: ApiVersion + '/companySale/save',
    companySaleEdit: ApiVersion + '/companySale/update',
    companySaleSingle: ApiVersion + '/companySale/single',
    companySaleDelete: ApiVersion + '/companySale/delete',
    companySaleList: ApiVersion + '/companySale/list',
    companySaleGetCategory: ApiVersion + '/companySale/getCategory',

    //invoice
    invoice: ApiVersion + '/invoice',
    invoiceGenerate: ApiVersion + '/invoice/generate',
    invoicePayment: ApiVersion + '/invoice/payment',
    invoiceGlobalPayment: ApiVersion + '/invoice/global/payment',
    invoiceEdit: ApiVersion + '/invoice/update',
    invoiceSingle: ApiVersion + '/invoice/single',
    invoiceDelete: ApiVersion + '/invoice/delete',
    invoiceList: ApiVersion + '/invoice/list',
    invoiceDownloadPdf: ApiVersion + '/invoice/download/pdf',
    invoiceDownloadExcel: ApiVersion + '/invoice/download/excel',
    //Dashboard
    getDashboard: ApiVersion + '/dashboard/get',
    //Report daily log
    dailyLog: ApiVersion + '/report/dailyLog',
    dailyLogPdf: ApiVersion + '/report/dailyLog/export/pdf',
    //Users
    userAdd: ApiVersion + '/user/save',
    userEdit: ApiVersion + '/user/update',
    userSingle: ApiVersion + '/user/single',
    userDelete: ApiVersion + '/user/delete',
    userList: ApiVersion + '/user/list',
    //Asset Transfer
    balanceTransferAdd: ApiVersion + '/balanceTransfer/save',
    balanceTransferEdit: ApiVersion + '/balanceTransfer/update',
    balanceTransferSingle: ApiVersion + '/balanceTransfer/single',
    balanceTransferDelete: ApiVersion + '/balanceTransfer/delete',
    balanceTransferList: ApiVersion + '/balanceTransfer/list',
    balanceTransferApprove: ApiVersion + '/balanceTransfer/approve',
    //System Company
    companyAdd: ApiVersion + '/company/save',
    companySingle: ApiVersion + '/company/single',

    // Voucher
    VoucherSave: ApiVersion + '/voucher/save',
    VoucherList: ApiVersion + '/voucher/list',

    // Driver
    DriverSave: ApiVersion + '/driver/save',
    DriverList: ApiVersion + '/driver/list',
    DriverSingle: ApiVersion + '/driver/single',
    DriverUpdate: ApiVersion + '/driver/update',
    DriverDelete: ApiVersion + '/driver/delete',
    DriverAmount: ApiVersion + '/driver/amount',

    //Role
    RoleList: ApiVersion + '/role/list',
    RoleSave: ApiVersion + '/role/save',
    RoleSingle: ApiVersion + '/role/single',
    RoleUpdate: ApiVersion + '/role/update',
    RoleDelete: ApiVersion + '/role/delete',

    PermissionList: ApiVersion + '/permission/list',
    //Adjustment
    FuelAdjustment: ApiVersion + '/fuelAdjustment/save',
    FuelAdjustmentList: ApiVersion + '/fuelAdjustment/list',
    FuelAdjustmentSingle: ApiVersion + '/fuelAdjustment/single',
    FuelAdjustmentUpdate: ApiVersion + '/fuelAdjustment/update',
    FuelAdjustmentDelete: ApiVersion + '/fuelAdjustment/delete',

    //sales report
    SalesReport: ApiVersion + '/report/sales',

    //Company Bills
    CompanyBillList: ApiVersion + '/companyBill/list',
    CompanyBillDownload: ApiVersion + '/companyBill/download',

    //car search
    CarSearch: ApiVersion + '/car/search',

    // car
    CarSave: ApiVersion + '/car/save',
    CarList: ApiVersion + '/car/list',
    CarSingle: ApiVersion + '/car/single',
    CarUpdate: ApiVersion + '/car/update',
    CarDelete: ApiVersion + '/car/delete',
    CarAmount: ApiVersion + '/car/amount',
    // Purchase Bill
    PurchaseSave: ApiVersion + '/purchase/save',
    PurchaseList: ApiVersion + '/purchase/list',
    PurchasePay: ApiVersion + '/purchase/pay',
    PurchaseSingle: ApiVersion + '/purchase/single',
    PurchaseUpdate: ApiVersion + '/purchase/update',
    PurchaseDelete: ApiVersion + '/purchase/delete',
    PurchaseAmount: ApiVersion + '/purchase/amount',
    InvoicePayment: ApiVersion + '/invoice/payment/list',
    Report: ApiVersion + '/report',
    BulkSale: ApiVersion + '/bulkSale',
    ShiftName: ApiVersion + '/shift/sale/shiftName/list',
    Profile: ApiVersion + '/profile/me',
    LoanStaffList: ApiVersion + '/loan/staff/list',
    StaffLoan: ApiVersion + '/staff/loan',
    Challan: ApiVersion + '/challan',
    LoanEntity: ApiVersion + '/loan-entity',
    CompanyLoan: ApiVersion + '/company-loan'
}

export default ApiRoutes;
