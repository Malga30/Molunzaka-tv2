import { useState } from 'react';

const useAsync = (asyncFunction, immediate = true) => {
  const [status, setStatus] = useState('idle');
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  const execute = async (...args) => {
    setStatus('pending');
    setData(null);
    setError(null);

    try {
      const response = await asyncFunction(...args);
      setData(response.data || response);
      setStatus('success');
      return response.data || response;
    } catch (err) {
      setError(err);
      setStatus('error');
      throw err;
    }
  };

  return { execute, status, data, error };
};

export default useAsync;
