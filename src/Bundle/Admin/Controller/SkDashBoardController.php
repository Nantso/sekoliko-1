<?php
/**
 * Created by PhpStorm.
 * User: julkwel
 * Date: 3/26/19
 * Time: 8:25 PM.
 */

namespace App\Bundle\Admin\Controller;

use App\Bundle\User\Entity\User;
use App\Shared\Entity\SkBook;
use App\Shared\Entity\SkClasse;
use App\Shared\Entity\SkClasseMatiere;
use App\Shared\Entity\SkConge;
use App\Shared\Entity\SkDisciplineList;
use App\Shared\Entity\SkEtudiant;
use App\Shared\Entity\SkMatiere;
use App\Shared\Entity\SkSalle;
use App\Shared\Services\Utils\RoleName;
use App\Shared\Services\Utils\ServiceName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SkDashBoardController extends Controller
{
    public function getEntityService()
    {
        return $this->get('sk.repository.entity');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPERADMIN')) {
            $_user_list = $this->get(ServiceName::SRV_METIER_USER)->getAllUser();
            $_nombre_user = count($_user_list);
            $_ets_count = count($this->get(ServiceName::SRV_METIER_USER)->findEtsList()->getQuery()->getResult());

            return $this->render('@Admin/SkDashboard/superadmin.html.twig', array(
                'nombre_user' => $_nombre_user,
                'ets_liste' => $_ets_count,
            ));
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')) {
            $_user_ets = $this->get('security.token_storage')->getToken()->getUser()->getEtsNom();
            $_user_as = $this->get('security.token_storage')->getToken()->getUser()->getAsName();
            $_user_name = $this->get('security.token_storage')->getToken()->getUser();

            $_matiere_liste = $this->getDoctrine()->getRepository(SkEtudiant::class)->findBy(array(
                'etudiant' => $_user_name,
                'asName' => $_user_as,
            ));
//            dump($_matiere_liste);die();
            if ($_matiere_liste) {
                $_etd_classe = $_matiere_liste[0]->getClasse()->getId();
                $_matiere_liste = count($this->getDoctrine()->getRepository(SkClasseMatiere::class)->findBy(['matClasse' => $_etd_classe, 'asName' => $_user_as]));
                $_etudiant_liste = count($this->getDoctrine()->getRepository(SkEtudiant::class)->findBy(array('classe' => $_etd_classe, 'asName' => $_user_as)));

                return $this->render('@Admin/SkDashboard/etudiant.dashboard.html.twig', array(
                    'mat_liste' => $_matiere_liste,
                    'liste_etudiant' => $_etudiant_liste,
                    'etudiant' => true,
                ));
            }

            return $this->render('@Admin/SkDashboard/etudiant.dashboard.html.twig', array(
                'error_classe' => 'Vous n\'etes pas encore assigné dans une classe',
                'etudiant' => false,
            ));
        } else {
            $_user_ets = $this->get('security.token_storage')->getToken()->getUser()->getEtsNom();
            $_user_as = $this->get('security.token_storage')->getToken()->getUser()->getAsName();

            $_conge_list = $this->getDoctrine()->getRepository(SkConge::class)->findBy([
                'etsNom' => $_user_ets,
                'asName' => $_user_as,
                'isFin' => false,
            ]);

//            dump($_conge_list);die();

            $_etd_filter = array(
                'skRole' => array(
                    RoleName::ID_ROLE_ETUDIANT,
                ),
                'etsNom' => $_user_ets,
            );
            $_prof_filter = array(
                'skRole' => array(
                    RoleName::ID_ROLE_PROFS,
                ),
                'etsNom' => $_user_ets,
                'asName' => $_user_as,
            );

            $_etd_list = $this->getDoctrine()->getRepository(User::class)->findBy($_etd_filter);
            $_prof_list = $this->getDoctrine()->getRepository(User::class)->findBy($_prof_filter);
            $_salle_list = $this->getDoctrine()->getRepository(SkSalle::class)->findBy(array('etsNom' => $_user_ets, 'asName' => $_user_as));
            $_classe_list = $this->getDoctrine()->getRepository(SkClasse::class)->findBy(array('etsNom' => $_user_ets, 'asName' => $_user_as));
            $_matiere_list = $this->getDoctrine()->getRepository(SkMatiere::class)->findBy(array('etsNom' => $_user_ets, 'asName' => $_user_as));
            $_discipline_list = $this->getDoctrine()->getRepository(SkDisciplineList::class)->findBy(array('etsNom' => $_user_ets, 'asName' => $_user_as));
            $_book_list = $this->getDoctrine()->getRepository(SkBook::class)->findBy(array('etsNom' => $_user_ets, 'asName' => $_user_as));

            $_list_etudiant = count($_etd_list);
            $_list_profs = count($_prof_list);
            $_list_salle = count($_salle_list);
            $_list_classe = count($_classe_list);

            return $this->render('@Admin/SkDashboard/index.html.twig', array(
                'etudiant' => $_list_etudiant,
                'profs' => $_list_profs,
                'salle' => $_list_salle,
                'classe' => $_list_classe,
                'ets' => $_user_ets,
                'congelist' => $_conge_list,
                'matiere' => $_matiere_list,
                'discipline' => $_discipline_list,
                'book' => $_book_list,
            ));
        }
    }
}
