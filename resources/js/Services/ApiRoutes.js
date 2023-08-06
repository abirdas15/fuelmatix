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
    ShiftSaleAdd: ApiVersion + '/shift/sale/save',
    ShiftSaleEdit: ApiVersion + '/shift/sale/update',
    ShiftSaleDelete: ApiVersion + '/shift/sale/delete',
    ShiftSaleList: ApiVersion + '/shift/sale/list',
    ShiftSaleSingle: ApiVersion + '/shift/sale/single',
    ShiftSaleGetCategory: ApiVersion + '/shift/sale/getCategory',
    //expense
    ExpenseAdd: ApiVersion + '/expense/save',
    ExpenseEdit: ApiVersion + '/expense/update',
    ExpenseDelete: ApiVersion + '/expense/delete',
    ExpenseList: ApiVersion + '/expense/list',
    ExpenseSingle: ApiVersion + '/expense/single',
    //Tank
    TankAdd: ApiVersion + '/tank/save',
    TankEdit: ApiVersion + '/tank/update',
    TankDelete: ApiVersion + '/tank/delete',
    TankList: ApiVersion + '/tank/list',
    TankSingle: ApiVersion + '/tank/single',
    TankGetNozzle: ApiVersion + '/tank/get/nozzle',
    //Tank Reading
    TankReadingAdd: ApiVersion + '/tank/reading/save',
    TankReadingEdit: ApiVersion + '/tank/reading/update',
    TankReadingDelete: ApiVersion + '/tank/reading/delete',
    TankReadingList: ApiVersion + '/tank/reading/list',
    TankReadingSingle: ApiVersion + '/tank/reading/single',
    TankReadingLatest: ApiVersion + '/tank/reading/latest',
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
    //Vendor
    VendorAdd: ApiVersion + '/vendor/save',
    VendorEdit: ApiVersion + '/vendor/update',
    VendorDelete: ApiVersion + '/vendor/delete',
    VendorList: ApiVersion + '/vendor/list',
    VendorSingle: ApiVersion + '/vendor/single',
    //Pay order
    PayOrderAdd: ApiVersion + '/pay/order/save',
    PayOrderEdit: ApiVersion + '/pay/order/update',
    PayOrderDelete: ApiVersion + '/pay/order/delete',
    PayOrderList: ApiVersion + '/pay/order/list',
    PayOrderSingle: ApiVersion + '/pay/order/single',
    PayOrderLatest: ApiVersion + '/pay/order/latest',
    //sale
    SaleAdd: ApiVersion + '/sale/save',
    SaleEdit: ApiVersion + '/sale/update',
    SaleSingle: ApiVersion + '/sale/single',
    SaleDelete: ApiVersion + '/sale/delete',
    SaleList: ApiVersion + '/sale/list',
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
    //Company sale
    companySaleAdd: ApiVersion + '/companySale/save',
    companySaleEdit: ApiVersion + '/companySale/update',
    companySaleSingle: ApiVersion + '/companySale/single',
    companySaleDelete: ApiVersion + '/companySale/delete',
    companySaleList: ApiVersion + '/companySale/list',
    companySaleGetCategory: ApiVersion + '/companySale/getCategory',

};

export default ApiRoutes;
