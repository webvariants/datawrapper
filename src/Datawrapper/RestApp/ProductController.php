<?php
/*
 * Copyright (c) 2014, Der Akademie Berufliche Bildung der deutschen Zeitungsverlage e. V.
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Datawrapper\RestApp;

use Datawrapper\ORM\ProductQuery;

class ProductController extends BaseController {
    /**
     * get list of all products
     */
    public function indexAction() {
        disable_cache($app);
        if_is_admin(function() use ($app) {
            try {
                $products = ProductQuery::create()->filterByDeleted(false)->find();
                $res      = array();
                foreach ($products as $product) {
                    $res[] = $product->toArray();
                }
                ok($res);
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * create new product
     */
    public function createAction() {
        disable_cache($app);
        // only admins can create products
        if_is_admin(function() use ($app) {
            try {
                $params  = json_decode($app->request()->getBody(), true);
                $product = new Product();
                $product->setName($params['name']);
                $product->setCreatedAt(time());

                if (isset($params['data'])) {
                    $product->setData(json_encode($params['data']));
                }

                $product->save();
                ok($product->toArray());
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * change product
     */
    public function updateAction() {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $params = json_decode($app->request()->getBody(), true);
                $product->setName($params['name']);
                $product->setData(json_encode($params['data']));
                $product->save();
                ok($product->toArray());
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    /**
     * delete product
     */
    public function deleteAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $product->setDeleted(true);
                $product->save();
                ok();
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    /**
     * add plugin to product
     */
    public function addPluginAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);

            if (!$product) {
                return error('unknown-product', 'Product not found');
            }

            try {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $pid) {
                    $plugin = PluginQuery::create()->findPk($pid);
                    if ($plugin && $plugin->getEnabled()) {
                        $product->addPlugin($plugin);
                    }
                }
                $product->save();
                ok();
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    /**
     * remove plugin from product
     */
    public function removePluginAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);

            if (!$product) {
                return error('unknown-product', 'Product not found');
            }

            try {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $pid) {
                    $plugin = PluginQuery::create()->findPk($pid);
                    if ($plugin && $product->hasPlugin($plugin)) {
                        $product->removePlugin($plugin);
                    }
                }
                $product->save();
                ok();
            } catch (Exception $e) {
                error('io-error', $e->getMessage());
            }
        });
    }

    public function addToUsersAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $newRelation) {
                    $user = UserQuery::create()->findPk($newRelation['id']);
                    if ($user) {
                        $up = new UserProduct();
                        $up->setUser($user);

                        if ($newRelation['expires']) {
                            $up->setExpires($newRelation['expires']);
                        }

                        $product->addUserProduct($up);
                    }
                }
                try {
                    $product->save();
                    ok($up);
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    public function updateUsersAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                $res  = array();

                try {
                    foreach ($data as $relation) {
                        $op = UserProductQuery::create()
                                ->filterByProductId($id)
                                ->filterByUserId($relation['id'])
                                ->findOne();
                        $op->setExpires($relation['expires']);
                        $op->save();
                    }
                    ok($res);
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    public function deleteFromUsersAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $userid) {
                    $org = UserQuery::create()->findPk($userid);
                    if ($org) {
                        $product->removeUser($org);
                    }
                }
                try {
                    $product->save();
                    ok();
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    public function addToOrganizationsAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                $res  = array();
                foreach ($data as $newRelation) {
                    $org = OrganizationQuery::create()->findPk($newRelation['id']);
                    if ($org) {
                        $op = new OrganizationProduct();
                        $op->setOrganization($org);

                        if ($newRelation['expires']) {
                            $op->setExpires($newRelation['expires']);
                        }

                        $product->addOrganizationProduct($op);
                    }
                    $res[] = $op;
                }
                try {
                    $product->save();
                    ok($res);
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    public function updateOrganizationsAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                $res  = array();

                try {
                    foreach ($data as $relation) {
                        $op = OrganizationProductQuery::create()
                                ->filterByProductId($id)
                                ->filterByOrganizationId($relation['id'])
                                ->findOne();
                        $op->setExpires($relation['expires']);
                        $op->save();
                    }
                    ok($res);
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }

    public function deleteFromOrganizationsAction($id) {
        if_is_admin(function() use ($app, $id) {
            $product = ProductQuery::create()->findPk($id);
            if ($product) {
                $data = json_decode($app->request()->getBody(), true);
                foreach ($data as $orgid) {
                    $org = OrganizationQuery::create()->findPk($orgid);
                    if ($org) {
                        $product->removeOrganization($org);
                    }
                }
                try {
                    $product->save();
                    ok();
                } catch (Exception $e) {
                    error('io-error', $e->getMessage());
                }
            } else {
                return error('unknown-product', 'Product not found');
            }
        });
    }
}