const ApiVersion = '/api/v1.0'
const ApiRoutes = {
    // Authentication
    Login: ApiVersion + '/auth/login',
    Logout: ApiVersion + '/auth/logout',
    Register: ApiVersion + '/auth/register',
    ForgotPassword: ApiVersion + '/auth/forgot',
    ResetPassword: ApiVersion + '/auth/reset/password',
    //Role
    role: ApiVersion + '/role/list',
    SaveRole: ApiVersion + '/role/save',
    UpdateRole: ApiVersion + '/role/update',
    DeleteRole: ApiVersion + '/role/delete',
    SingleRole: ApiVersion + '/role/single',
    //Access
    SaveAccess: ApiVersion + '/access/save',
    GetAccess: ApiVersion + '/access/get',
    //Module
    GetModule: ApiVersion + '/module/get',
    //Agent
    GetAgent: ApiVersion + '/agent/list',
    GetAgentAll: ApiVersion + '/agent/all',
    SaveAgent: ApiVersion + '/agent/save',
    SingleAgent: ApiVersion + '/agent/single',
    UpdateAgent: ApiVersion + '/agent/update',
    DeleteAgent: ApiVersion + '/agent/delete',
    //customer
    GetCustomer: ApiVersion + '/customer/list',
    GetCustomerAll: ApiVersion + '/customer/all',
    SaveCustomer: ApiVersion + '/customer/save',
    SingleCustomer: ApiVersion + '/customer/single',
    UpdateCustomer: ApiVersion + '/customer/update',
    DeleteCustomer: ApiVersion + '/customer/delete',
    AddContact: ApiVersion + '/customer/add/contact',
    //ticket
    GetTicket: ApiVersion + '/ticket/list',
    SaveTicket: ApiVersion + '/ticket/save',
    SingleTicket: ApiVersion + '/ticket/single',
    UpdateTicket: ApiVersion + '/ticket/update',
    DeleteTicket: ApiVersion + '/ticket/delete',
    LatestTicket: ApiVersion + '/ticket/latest',
    UpdateStatusTicket: ApiVersion + '/ticket/change/status',
    AllTicket: ApiVersion + '/ticket/all',
    MergeTicket: ApiVersion + '/ticket/merge',
    ViewTicket: ApiVersion + '/ticket/view',
    RemoveTicketUser: ApiVersion + '/ticket/user/remove',
    TagSave: ApiVersion + '/ticket/tag/save',
    TagList: ApiVersion + '/ticket/tag/list',
    TicketTagDelete: ApiVersion + '/ticket/tag/delete',
    //conversation
    GetConversation: ApiVersion + '/conversation/list',
    SaveConversation: ApiVersion + '/conversation/save',
    SingleConversation: ApiVersion + '/conversation/single',
    UpdateConversation: ApiVersion + '/conversation/update',
    DeleteConversation: ApiVersion + '/conversation/delete',
    //Tag
    GetTag: ApiVersion + '/tag/list',
    SaveTag: ApiVersion + '/tag/save',
    UpdateTag: ApiVersion + '/tag/update',
    DeleteTag: ApiVersion + '/tag/delete',
    SingleTag: ApiVersion + '/tag/single',
    //Note
    GetNote: ApiVersion + '/call/note/list',
    SaveNote: ApiVersion + '/call/note/save',
    UpdateNote: ApiVersion + '/call/note/update',
    DeleteNote: ApiVersion + '/call/note/delete',
    SingleNote: ApiVersion + '/call/note/single',
    //log
    GetLog: ApiVersion + '/log/call',
    GetLogAgent: ApiVersion + '/log/agent',
    GetDailySummery: ApiVersion + '/log/daily/summary',
    //dashboard
    GetDashboard: ApiVersion + '/dashboard/get',
    //Replies
    GetReplies: ApiVersion + '/quick/reply/list',
    SaveReplies: ApiVersion + '/quick/reply/save',
    UpdateReplies: ApiVersion + '/quick/reply/update',
    DeleteReplies: ApiVersion + '/quick/reply/delete',
    SingleReplies: ApiVersion + '/quick/reply/single',
    AllReplies: ApiVersion + '/quick/reply/all',
    //Report
    ServicePerformance: ApiVersion + '/report/service/performance',
    Productivity: ApiVersion + '/report/productivity',
    HourlyFrequency: ApiVersion + '/report/hourly/frequency',
    AgentPerformance: ApiVersion + '/report/agent/performance',
    Csat: ApiVersion + '/report/csat',
    //Report PDF
    ServicePerformancePDF: ApiVersion + '/report/pdf/service/performance',
    ProductivityPDF: ApiVersion + '/report/pdf/productivity',
    HourlyFrequencyPDF: ApiVersion + '/report/pdf/hourly/frequency',
    AgentPerformancePDF: ApiVersion + '/report/pdf/agent/performance',
    CsatPDF: ApiVersion + '/report/pdf/csat',
    //Report xls
    ServicePerformanceXls: ApiVersion + '/report/excel/service/performance',
    ProductivityXls: ApiVersion + '/report/excel/productivity',
    HourlyFrequencyXls: ApiVersion + '/report/excel/hourly/frequency',
    AgentPerformanceXls: ApiVersion + '/report/excel/agent/performance',
    CsatXls: ApiVersion + '/report/excel/csat',
};

export default ApiRoutes;
