import useAuth from '../hooks/useAuth';

const DashboardPage = () => {
  const { user } = useAuth();

  return (
    <div className="space-y-8">
      <div className="bg-white rounded-lg shadow p-6">
        <h1 className="text-3xl font-bold text-gray-800 mb-2">
          Welcome back, {user?.first_name}!
        </h1>
        <p className="text-gray-600">
          You're logged in to Molunzaka TV
        </p>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">
            👤 Profile
          </h2>
          <p className="text-gray-600 mb-4">
            Manage your profile and account settings
          </p>
          <button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition">
            View Profile
          </button>
        </div>

        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">
            🎬 My Videos
          </h2>
          <p className="text-gray-600 mb-4">
            View and manage your uploaded videos
          </p>
          <button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition">
            Browse Videos
          </button>
        </div>

        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">
            📊 Statistics
          </h2>
          <p className="text-gray-600 mb-4">
            View your viewing history and statistics
          </p>
          <button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition">
            View Stats
          </button>
        </div>

        <div className="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">
            ⚙️ Settings
          </h2>
          <p className="text-gray-600 mb-4">
            Configure your account preferences
          </p>
          <button className="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded transition">
            Go to Settings
          </button>
        </div>
      </div>
    </div>
  );
};

export default DashboardPage;
