import { useState } from 'react';
import { 
  ArrowLeft, 
  Filter, 
  Search, 
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle,
  TrendingDown,
  DollarSign,
  PieChart,
  BarChart3
} from 'lucide-react';

// Componente para a página de saques
const AccountWithdraw = () => {
  // Dados de exemplo fornecidos
  const withdrawData = {
    "data": {
      "items": [
        {
          "id": "121f926c-314c-4e9f-9f36-cb1a8bee2ed8",
          "account_id": "0010e543-1ccf-46de-93af-c2b1c2bd78be",
          "method": "pix",
          "amount": 1000,
          "scheduled": true,
          "scheduled_for": "2025-08-25 01:18:05",
          "done": false,
          "error": false,
          "error_reason": null,
          "created_at": "2025-08-24 22:18:06",
          "updated_at": "2025-08-24 22:18:06"
        },
        {
          "id": "64c87e43-5ce5-49bf-9a49-78b996b0bcef",
          "account_id": "0010e543-1ccf-46de-93af-c2b1c2bd78be",
          "method": "pix",
          "amount": 1000,
          "scheduled": true,
          "scheduled_for": "2025-08-25 00:00:00",
          "done": false,
          "error": false,
          "error_reason": null,
          "created_at": "2025-08-24 22:18:06",
          "updated_at": "2025-08-24 22:18:06"
        },
        {
          "id": "6802f030-e220-4dfd-a6df-e58bb5fb12ba",
          "account_id": "0010e543-1ccf-46de-93af-c2b1c2bd78be",
          "method": "pix",
          "amount": 1000,
          "scheduled": false,
          "scheduled_for": null,
          "done": true,
          "error": false,
          "error_reason": null,
          "created_at": "2025-08-24 22:18:05",
          "updated_at": "2025-08-24 22:18:05"
        }
      ],
      "total": 3,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  };

  const [filterStatus, setFilterStatus] = useState('all');
  const [filterMethod, setFilterMethod] = useState('all');

  // Função para formatar data
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
  };

  // Função para formatar hora
  const formatTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  };

  // Função para formatar valor monetário
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  };

  // Filtrar saques com base nos filtros selecionados
  const filteredWithdraws = withdrawData.data.items.filter(item => {
    const statusMatch = filterStatus === 'all' || 
      (filterStatus === 'scheduled' && item.scheduled && !item.done) ||
      (filterStatus === 'completed' && item.done) ||
      (filterStatus === 'failed' && item.error);
    
    const methodMatch = filterMethod === 'all' || item.method === filterMethod;
    
    return statusMatch && methodMatch;
  });

  // Calcular totais para o dashboard
  const totalWithdraws = withdrawData.data.items.length;
  const totalAmount = withdrawData.data.items.reduce((sum, item) => sum + item.amount, 0);
  const completedWithdraws = withdrawData.data.items.filter(item => item.done).length;
  const scheduledWithdraws = withdrawData.data.items.filter(item => item.scheduled && !item.done).length;

  return (
    <div className="min-h-screen">
      <div className="max-w-7xl mx-auto">
        {/* Cabeçalho */}
        <div className="mb-8 flex items-center text-start">
          <div>
            <button 
              onClick={() => window.history.back()} 
              className="flex items-center text-blue-600 hover:text-blue-800 mb-2"
            >
              <ArrowLeft className="w-5 h-5 mr-2" />
              Voltar para contas
            </button>
            <h1 className="text-3xl font-bold text-gray-900">Saques da Conta</h1>
            <p className="text-gray-600 mt-2">Acompanhe todos os saques realizados</p>
          </div>
        </div>

        {/* Dashboard de métricas */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="bg-white rounded-xl shadow-sm p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-500 text-sm">Total de Saques</p>
                <p className="text-2xl font-bold text-gray-900">{totalWithdraws}</p>
              </div>
              <div className="bg-blue-100 p-3 rounded-full">
                <TrendingDown className="w-6 h-6 text-blue-600" />
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-500 text-sm">Valor Total</p>
                <p className="text-2xl font-bold text-gray-900">{formatCurrency(totalAmount)}</p>
              </div>
              <div className="bg-green-100 p-3 rounded-full">
                <DollarSign className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-500 text-sm">Concluídos</p>
                <p className="text-2xl font-bold text-gray-900">{completedWithdraws}</p>
              </div>
              <div className="bg-purple-100 p-3 rounded-full">
                <CheckCircle className="w-6 h-6 text-purple-600" />
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-500 text-sm">Agendados</p>
                <p className="text-2xl font-bold text-gray-900">{scheduledWithdraws}</p>
              </div>
              <div className="bg-yellow-100 p-3 rounded-full">
                <Clock className="w-6 h-6 text-yellow-600" />
              </div>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <div className="bg-white rounded-xl shadow-sm p-6 mb-6">
          <div className="flex flex-col md:flex-row md:items-center gap-4 mb-3">
            <div className="flex-1 relative">
              <Search className="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <input
                type="text"
                placeholder="Buscar por método, valor, data..."
                className="w-full pl-10 pr-4 py-2 text-gray-500 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            
            <div className="flex flex-col sm:flex-row gap-3">
              <div className="relative">
                <select
                  className="appearance-none text-gray-500 bg-white border border-gray-300 rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  value={filterStatus}
                  onChange={(e) => setFilterStatus(e.target.value)}
                >
                  <option value="all">Todos os status</option>
                  <option value="scheduled">Agendados</option>
                  <option value="completed">Concluídos</option>
                  <option value="failed">Com erro</option>
                </select>
                <Filter className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
              </div>
              
              <div className="relative">
                <select
                  className="appearance-none text-gray-500 bg-white border border-gray-300 rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  value={filterMethod}
                  onChange={(e) => setFilterMethod(e.target.value)}
                >
                  <option value="all">Todos os métodos</option>
                  <option value="pix">PIX</option>
                  <option value="ted">TED</option>
                  <option value="doc">DOC</option>
                </select>
                <Filter className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>
          </div>

          {/* Lista de saques */}
          <div className="overflow-x-auto">
            <table className="w-full border">
              <thead className="bg-gray-100">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Método
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Valor
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Data
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider">
                    Agendamento
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredWithdraws.length > 0 ? (
                  filteredWithdraws.map((withdraw) => (
                    <tr key={withdraw.id} className="hover:bg-gray-50 transition-colors">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center">
                          <div className={`p-2 rounded-full mr-3 ${
                            withdraw.method === 'pix' ? 'bg-purple-100' : 
                            withdraw.method === 'ted' ? 'bg-blue-100' : 'bg-green-100'
                          }`}>
                            {withdraw.method === 'pix' ? (
                              <BarChart3 className="w-5 h-5 text-purple-600" />
                            ) : withdraw.method === 'ted' ? (
                              <TrendingDown className="w-5 h-5 text-blue-600" />
                            ) : (
                              <DollarSign className="w-5 h-5 text-green-600" />
                            )}
                          </div>
                          <div>
                            <div className="text-sm font-medium text-gray-900 capitalize">
                              {withdraw.method}
                            </div>
                            <div className="text-xs text-gray-500">
                              {formatTime(withdraw.created_at)}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-semibold text-red-600">
                          - {formatCurrency(withdraw.amount)}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        {withdraw.error ? (
                          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <XCircle className="w-4 h-4 mr-1" />
                            Erro
                          </span>
                        ) : withdraw.done ? (
                          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <CheckCircle className="w-4 h-4 mr-1" />
                            Concluído
                          </span>
                        ) : withdraw.scheduled ? (
                          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <Clock className="w-4 h-4 mr-1" />
                            Agendado
                          </span>
                        ) : (
                          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <AlertCircle className="w-4 h-4 mr-1" />
                            Processando
                          </span>
                        )}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{formatDate(withdraw.created_at)}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        {withdraw.scheduled ? (
                          <div className="flex items-center">
                            <Calendar className="w-4 h-4 text-gray-400 mr-1" />
                            <span className="text-sm text-gray-900">
                              {formatDate(withdraw.scheduled_for)} {formatTime(withdraw.scheduled_for)}
                            </span>
                          </div>
                        ) : (
                          <span className="text-sm text-gray-500">-</span>
                        )}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="5" className="px-6 py-8 text-center">
                      <div className="flex flex-col items-center justify-center text-gray-500">
                        <Filter className="w-12 h-12 mb-2 opacity-30" />
                        <p className="text-lg">Nenhum saque encontrado</p>
                        <p className="text-sm mt-1">Tente ajustar seus filtros</p>
                      </div>
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AccountWithdraw;