const HomePage = () => {
  return (
    <div className="space-y-12">
      {/* Hero Section */}
      <div className="bg-gradient-to-r from-primary-600 to-primary-800 text-white rounded-lg shadow-lg p-12 text-center">
        <h1 className="text-5xl font-bold mb-4">
          Welcome to Molunzaka TV
        </h1>
        <p className="text-xl mb-8 text-primary-100">
          Your premium streaming platform for unlimited entertainment
        </p>
        <div className="flex gap-4 justify-center">
          <a
            href="/login"
            className="bg-white text-primary-600 hover:bg-primary-50 px-8 py-3 rounded-lg font-semibold transition"
          >
            Login
          </a>
          <a
            href="/register"
            className="bg-primary-700 hover:bg-primary-600 px-8 py-3 rounded-lg font-semibold transition border-2 border-white"
          >
            Sign Up
          </a>
        </div>
      </div>

      {/* Features Section */}
      <div>
        <h2 className="text-3xl font-bold text-gray-800 mb-8">Features</h2>
        <div className="grid md:grid-cols-3 gap-6">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-4xl mb-4">🎬</div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">
              Unlimited Streaming
            </h3>
            <p className="text-gray-600">
              Access thousands of movies and shows anytime, anywhere
            </p>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-4xl mb-4">📱</div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">
              Watch Anywhere
            </h3>
            <p className="text-gray-600">
              Stream on your phone, tablet, or smart TV
            </p>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-4xl mb-4">👥</div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">
              Multiple Profiles
            </h3>
            <p className="text-gray-600">
              Create custom profiles for everyone in your household
            </p>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="bg-primary-50 rounded-lg p-12 text-center">
        <h2 className="text-3xl font-bold text-gray-800 mb-4">
          Ready to get started?
        </h2>
        <p className="text-gray-600 mb-6 text-lg">
          Join millions of users enjoying premium content on Molunzaka TV
        </p>
        <a
          href="/register"
          className="inline-block bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-lg font-semibold transition"
        >
          Sign Up for Free
        </a>
      </div>
    </div>
  );
};

export default HomePage;
