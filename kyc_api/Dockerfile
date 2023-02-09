FROM node:alpine

WORKDIR /usr/local/server
RUN chown node:node ./
USER node
COPY package.json ./
RUN npm i && npm cache clean --force
COPY ./ /usr/local/server
CMD ["npm", "run", "start"]