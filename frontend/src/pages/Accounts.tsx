import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  Search, 
  Filter, 
  Plus, 
  Edit, 
  Trash2, 
  Eye,
  ChevronDown,
  ChevronUp,
  FileText,
  LogOut
} from 'lucide-react';

const AccountListPage = () => {
  const navigate = useNavigate();
  const [sortField, setSortField] = useState('name');
  const [sortDirection, setSortDirection] = useState('asc');
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('all');

  // Dados de exemplo
  const accounts = [
    { id: 1, name: 'Conta Corrente', bank: 'Banco do Brasil', balance: 12500.75, status: 'active', number: '12345-6' },
    { id: 2, name: 'Conta Poupança', bank: 'Itaú', balance: 35780.32, status: 'active', number: '78910-1' },
    { id: 3, name: 'Investimentos', bank: 'XP Investimentos', balance: 89500.15, status: 'active', number: '111222-3' },
    { id: 4, name: 'Conta Conjunta', bank: 'Santander', balance: 5200.00, status: 'inactive', number: '444555-7' },
    { id: 5, name: 'Conta Empresarial', bank: 'Bradesco', balance: 28765.43, status: 'active', number: '888999-0' },
  ];

  const statusOptions = [
    { value: 'all', label: 'Todos os status' },
    { value: 'active', label: 'Ativo' },
    { value: 'inactive', label: 'Inativo' },
  ];

  const handleSort = (field) => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortDirection('asc');
    }
  };

  const filteredAccounts = accounts
    .filter(account => 
      account.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      account.bank.toLowerCase().includes(searchTerm.toLowerCase()) ||
      account.number.includes(searchTerm)
    )
    .filter(account => selectedStatus === 'all' || account.status === selectedStatus);

  const sortedAccounts = [...filteredAccounts].sort((a, b) => {
    const modifier = sortDirection === 'asc' ? 1 : -1;
    if (a[sortField] < b[sortField]) return -1 * modifier;
    if (a[sortField] > b[sortField]) return 1 * modifier;
    return 0;
  });

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  };

  const getStatusBadgeClass = (status) => {
    return status === 'active' 
      ? 'bg-green-100 text-green-800' 
      : 'bg-red-100 text-red-800';
  };

  return (
    <div className="min-h-screen">
      <div className="max-w-7xl mx-auto">
        {/* Cabeçalho */}
        <div className="mb-8 flex justify-between items-start text-start">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Contas Bancárias</h1>
            <p className="text-gray-600 mt-2">Gerencie todas as suas contas em um único lugar</p>
          </div>
          
          <div className="flex flex-row gap-2">
            <a 
              href="http://localhost:9501/swagger/index.html" target="_blank"
              className="flex items-center gap-2 text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition-colors"
            >
              <FileText className="w-5 h-5" />
              Acessar Swagger
            </a>
            <button 
              onClick={() => {navigate("/")}}
              className="flex items-center gap-2 text-white bg-gray-400 hover:bg-gray-500 px-4 py-2 rounded-lg transition-colors"
            >
              <LogOut className="w-5 h-5" />
              Sair
            </button>
          </div>
        </div>

        {/* Barra de ações */}
        <div className="bg-white rounded-xl shadow-sm p-6 mb-6">
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div className="flex-1">
              <div className="flex-1 relative">
                <Search className="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <input
                  type="text"
                  placeholder="Buscar por nome, banco, saldo..."
                  className="w-full pl-10 pr-4 py-2 text-gray-500 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            
            <div className="flex items-center gap-3">
              <div className="relative">
                <select
                  className="appearance-none text-gray-500 bg-white border border-gray-300 rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  value={selectedStatus}
                  onChange={(e) => setSelectedStatus(e.target.value)}
                >
                  {statusOptions.map(option => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
                <Filter className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
              </div>
              
              <button className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <Plus className="w-5 h-5" />
                Nova Conta
              </button>
            </div>
          </div>
        </div>

        {/* Tabela de contas */}
        <div className="bg-white rounded-xl shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-100">
                <tr>
                  <th 
                    className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSort('name')}
                  >
                    <div className="flex items-center gap-1">
                      Nome da Conta
                      {sortField === 'name' && (
                        sortDirection === 'asc' ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />
                      )}
                    </div>
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Banco
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Número
                  </th>
                  <th 
                    className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSort('balance')}
                  >
                    <div className="flex items-center gap-1">
                      Saldo
                      {sortField === 'balance' && (
                        sortDirection === 'asc' ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />
                      )}
                    </div>
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {sortedAccounts.length > 0 ? (
                  sortedAccounts.map((account) => (
                    <tr key={account.id} className="hover:bg-gray-50 transition-colors">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">{account.name}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{account.bank}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-500">{account.number}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-semibold text-gray-900">
                          {formatCurrency(account.balance)}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusBadgeClass(account.status)}`}>
                          {account.status === 'active' ? 'Ativo' : 'Inativo'}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div className="flex justify-end items-center gap-2">
                          <button 
                          onClick={() => navigate(`/accounts/withdraws`, { state: { from: location.pathname } })}
                          className="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50">
                            <Eye className="w-4 h-4" />
                          </button>
                          <button className="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50">
                            <Edit className="w-4 h-4" />
                          </button>
                          <button className="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50">
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="6" className="px-6 py-8 text-center">
                      <div className="flex flex-col items-center justify-center text-gray-500">
                        <p className="text-lg">Nenhuma conta encontrada</p>
                        <p className="text-sm mt-1">Tente ajustar seus filtros de busca</p>
                      </div>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
          
          {/* Rodapé da tabela */}
          {sortedAccounts.length > 0 && (
            <div className="px-6 py-4 bg-gray-100 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between">
              <p className="text-sm text-gray-700 mb-4 sm:mb-0">
                Mostrando <span className="font-medium">{sortedAccounts.length}</span> de{' '}
                <span className="font-medium">{sortedAccounts.length}</span> resultados
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AccountListPage;