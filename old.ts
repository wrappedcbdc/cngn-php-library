class Jump {
    
    private async handleApiError(error: any): Promise<never> {
        if (error.response) {
            throw new Error(`API Error: ${error.response.status} - ${error.response.data.message || 'Unknown error'}`);
        }
        else if (error.request) {
            throw new Error('No response received from API');
        }
        else {
            throw new Error(`Error setting up request: ${error.message}`);
        }
    }

    private async makeCalls<T>(method: string, endpoint: string, data?: any): Promise<IResponse<T>> {
        try {
            const response: AxiosResponse<IResponse<T>> = await this.axiosInstance.request({
                method,
                url: endpoint,
                data: data ? AESCrypto.encrypt(JSON.stringify(data), this.secrets.encryptionKey) : undefined
            });
            if (typeof response.data.data === 'string') {
                const decryptedData = await Ed25519Crypto.decryptWithPrivateKey(this.secrets.privateKey, response.data.data);
                return {
                    ...response.data,
                    data: JSON.parse(decryptedData) as T
                };
            }

            return response.data as IResponse<T>;
        }
        catch (error) {
            throw await this.handleApiError(error);
        }
    }

    public async getBalance(): Promise<IResponse<Balance[]>> {
        return this.makeCalls('GET', '/balance');
    }

    public async getTransactionHistory(): Promise<IResponse<Transactions[]>> {
        return this.makeCalls('GET', '/transactions');
    }

    public async swapBetweenChains(data: SwapParams): Promise<IResponse<IWithdraw>> {
        return this.makeCalls('POST', '/swap', data);
    }

    public async depositForRedemption(data: DepositParams): Promise<IResponse<Transactions>> {
        return this.makeCalls('POST', '/deposit', data);
    }

    public async createVirtualAccount(data: MintParams): Promise<IResponse<IVirtualAccount>> {
        return this.makeCalls('POST', '/createVirtualAccount', data);
    }

    public async whitelistAddress(data: WhiteListAddressParams): Promise<IResponse<ExternalAccounts>> {
        return this.makeCalls('POST', '/whiteListAddress', data);
    }
}